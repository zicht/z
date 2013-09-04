<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Tool\Container;

use \Zicht\Tool\Script;
use \Zicht\Tool\PluginInterface;
use \UnexpectedValueException;
use \Symfony\Component\Console\Output\OutputInterface;
use \Symfony\Component\Process\Process;
use Zicht\Tool\Script\Compiler as ScriptCompiler;

/**
 * Service container
 */
class Container
{
    const SHELL = '/bin/bash';

    protected $plugins = array();
    protected $subscribers = array();

    public $output;

    // TODO check if config really needs to be public
    public $config;

    public $definition = '';

    protected $values = array();
    protected $declarations = array();
    protected $functions = array();

    protected $stack = array();
    protected $prefix = array();

    /**
     * Construct the container with the specified values as services/values.
     *
     * @param array $values
     */
    public function __construct($vars, $config)
    {
        $this->values = $vars;
        $this->config = $config;

        $this->subscribe(array($this, 'prefixListener'));

        $this->fn('is_file');
        $this->fn('is_dir');
        $this->fn('filemtime');

        if (!$this->has('explain')) {
            $this->set('explain', false);
        }
        if (!$this->has('verbose')) {
            $this->set('verbose', false);
        }
        if (!$this->has('force')) {
            $this->set('force', false);
        }
        $this->set('interactive', false);

        // gather the options for nested z calls.
        $opts = array();
        foreach (array('force', 'verbose', 'explain') as $opt) {
            if ($this->values[$opt]) {
                $opts[]= '--' . $opt;
            }
        }
        $this->set('z.opts', join(' ', $opts));
    }


    public function resolve($id)
    {
        if (in_array($id, $this->stack)) {
            throw new \UnexpectedValueException(
                sprintf(
                    "Circular reference detected: %s->%s",
                    implode(' -> ', $this->stack),
                    $id
                )
            );
        }

        array_push($this->stack, $id);
        if (!array_key_exists($id, $this->values)) {
            if (array_key_exists($id, $this->declarations)) {
                $this->values[$id] = call_user_func($this->declarations[$id], $this);
            } else {
                array_pop($this->stack);
                throw new \InvalidArgumentException("Unresolvable value [$id]");
            }
        }
        array_pop($this->stack);

        return $this->values[$id];
    }


    public function set($id, $value)
    {
        $this->values[$id] = $value;
    }


    public function fn($id, $callable = null, $needsContainer = false)
    {
        if ($callable === null) {
            $callable = $id;
        }
        if (!is_callable($callable)) {
            throw new \InvalidArgumentException("Not callable");
        }
        $this->functions[$id] = array($callable, $needsContainer);
    }


    public function method($id, $callable)
    {
        $this->fn($id, $callable, true);
    }


    public function decl($id, $callable)
    {
        if (!is_callable($callable)) {
            throw new \InvalidArgumentException("Not callable");
        }
        $this->declarations[$id] = $callable;
    }


    public function evaluate($script)
    {
        $exprcompiler  = new ScriptCompiler(
            new \Zicht\Tool\Script\Parser\Expression(),
            new \Zicht\Tool\Script\Tokenizer\Expression()
        );

        $z = $this;
        $_value = null;
        eval('$_value = ' . $exprcompiler->compile($script) . ';');
        return $_value;
    }


    public function has($id)
    {
        return !empty($this->values[$id]);
    }


    /**
     * Separate helper for calling a service as a function.
     *
     * @return mixed
     * @throws \InvalidArgumentException
     */
    public function call()
    {
        $args = func_get_args();
        $service = array_shift($args);
        $service = $this->functions[$service];
        if (!is_callable($service[0])) {
            throw new \InvalidArgumentException("Can not use service '$service' as a function, it is not callable");
        }

        // if the service needs the container, it is specified in the decl() call as the second param:
        if ($service[1]) {
            array_unshift($args, $this);
        }

        return call_user_func_array($service[0], $args);
    }


    function prefixListener($task, $event)
    {
        if ($this->resolve('explain') && !$this->resolve('verbose')) {
            // don't do prefixing if the "explain" option is given, unless the "verbose" option is given too
            // in the latter case we do want the prefixes (because then we would want to know why certain
            // pieces are executed
            return;
        }
        $reset = false;
        switch ($event) {
            case 'start':
                $this->prefix[]= (string)$task;
                $reset = true;
                break;
            case 'end':
                $this->prefix = array_slice($this->prefix, 0, -1);
                $reset = true;
                break;
        }

        if ($reset) {
            if ($this->output->getVerbosity() > 1) {
                $this->output->setPrefix('<info>[' . join('][', $this->prefix) . ']</info> ');
            } elseif (count($this->prefix) > 1) {
                $prefix = $this->prefix[count($this->prefix) -1];
                if (strlen($prefix) > 21) {
                    $prefix = substr($prefix, 0, 9) . '...' . substr($prefix, -9);
                }
                $prefix = str_pad($prefix, 21, ' ', STR_PAD_LEFT);
                $this->output->setPrefix('<info>[' . $prefix . ']</info> ');
            } else {
                $this->output->setPrefix('');
            }
        }
    }


    /**
     * Executes a script snippet using the 'executor' service.
     *
     * @param string $cmd
     * @return void
     */
    public function exec($cmd)
    {
        if (trim($cmd)) {
            if ($this->resolve('explain')) {
                $this->output->writeln('( ' . rtrim($cmd, "\n") . ' );');
            } else {
                $this->doExec($cmd);
            }
        }
    }


    /**
     * Method to do an actual shell command. It uses 'passthru' if 'interactive' is set, otherwise uses a
     * process wrapper.
     *
     * @param string $cmd
     * @return int
     *
     * @throws \UnexpectedValueException
     */
    protected function doExec($cmd)
    {
        if ($this->resolve('interactive')) {
            passthru($cmd, $ret);
        } else {
            $process = new Process(self::SHELL);
            $process->setTimeout(null);
            $process->setStdin($cmd);
            $process->run(array($this, 'processCallback'));
            $ret = $process->getExitCode();
        }
        if ($ret != 0) {
            throw new \UnexpectedValueException("Command '$cmd' failed with exit code {$ret}");
        }
        return $ret;
    }


    /**
     * This is useful for commands that need the shell regardless of the 'explain' value setting.
     *
     * @param string $cmd
     */
    public function helperExec($cmd)
    {
        if ($this->resolve('explain')) {
            $this->output->writeln("# Task needs the following helper command:");
            $this->output->writeln("# " . str_replace("\n", "\\n", $cmd));
        }
        $this->doExec($cmd);
    }


    public function processCallback($mode, $data)
    {
        if (isset($this->output)) {
            $this->output->write($data);
        }
    }


    /**
     * Execute a command. This is a wrapper for 'exec', so that a task prefixed with '@' can be passed as well.
     *
     * @param string $cmd
     * @return int
     * @return int
     */
    public function cmd($cmd)
    {
        $cmd = ltrim($cmd);
        if (substr($cmd, 0, 1) === '@') {
            return $this->resolve('tasks.' . substr($cmd, 1));
        }
        return $this->exec($cmd);
    }


    /**
     * Select a nested configuration for aliased use, typically for 'env'
     *
     * @param string $namespace
     * @param string $key
     * @return void
     */
    public function select($namespace, $key)
    {
        $this->values[$namespace] = $key;
        if (!isset($this->config[$namespace][$key])) {
            throw new \InvalidArgumentException("Invalid {$namespace} provided, {$namespace}.{$key} is not defined");
        }
        foreach ($this->config[$namespace][$key] as $name => $value) {
            $this->values[$namespace . '.' . $name] = $value;
        }
    }


    /**
     * Returns the value representation of the requested variable
     *
     * @param string $value
     * @return string
     */
    public function value($value)
    {
        if (is_array($value)) {
            return join(' ', $value);
        }
        return (string)$value;
    }


    /**
     * Returns all registered plugins
     *
     * @return PluginInterface[]
     */
    public function getPlugins()
    {
        return $this->plugins;
    }


    /**
     * Notifies registered subscribers of an event
     *
     * @param string $task
     * @param string $event
     * @return void
     */
    public function notify($task, $event)
    {
        foreach ($this->subscribers as $subscriber) {
            call_user_func($subscriber, $task, $event, $this);
        }
    }


    /**
     * Subscribe to the events dispatched by notify()
     *
     * @param callable $callback
     * @return void
     */
    public function subscribe($callback)
    {
        $this->subscribers[]= $callback;
    }
}