<?php
/**
 * @copyright Zicht Online <https://zicht.nl>
 */

namespace Zicht\Tool\Container;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input;
use Symfony\Component\Console\Output;
use Symfony\Component\Console\Style\SymfonyStyle;
use Zicht\Tool\Debug;
use Zicht\Tool\Output\PrefixFormatter;
use Zicht\Tool\PluginInterface;
use Zicht\Tool\PropertyPath\PropertyAccessor;
use Zicht\Tool\Script\Compiler as ScriptCompiler;
use Zicht\Tool\Script\Parser\Expression as ExpressionParser;
use Zicht\Tool\Script\Tokenizer\Expression as ExpressionTokenizer;
use Zicht\Tool\Util;

/**
 * Service container
 */
class Container
{
    /**
     * Exit code used by commands to identify that they should abort the entire script
     */
    const ABORT_EXIT_CODE = 42;

    protected $commands = array();
    protected $values = array();

    private $resolutionStack = array();
    private $varStack = array();
    private $scope = array();

    /** @var Output\OutputInterface */
    public $output;

    /** @var Executor */
    public $executor;

    /** @var PluginInterface[] */
    public $plugins = array();

    /**
     * Construct the container with the specified values as services/values.
     *
     * @param Executor|null $executor
     * @param Output\OutputInterface|null $output
     */
    public function __construct(Executor $executor = null, Output\OutputInterface $output = null)
    {
        $this->executor = $executor ?: new Executor($this);
        $this->output = $output ?: new Output\NullOutput();

        $this->values = array(
            'DEFAULT_SHELL' => '/bin/bash -el',
            'SHELL'         => function (Container $z) {
                return $z->get('DEFAULT_SHELL');
            },
            'TIMEOUT'       => null,
            'INTERACTIVE'   => false,
        );
        // gather the options for nested z calls.
        $this->set(
            array('z', 'opts'),
            function (Container $z) {
                $opts = array();
                foreach (array('FORCE', 'VERBOSE', 'EXPLAIN', 'DEBUG') as $opt) {
                    if ($z->has($opt) && $z->get($opt)) {
                        $opts[] = '--' . strtolower($opt);
                    }
                }
                return join(' ', $opts);
            }
        );
        $this->set(array('z', 'cmd'), $_SERVER['argv'][0]);
        $this->decl(
            'STDIN',
            function () {
                return stream_get_contents(STDIN);
            }
        );
        $this->fn('confirm', function () {
            return false;
        });
        $this->set('cwd', getcwd());
        $this->set('user', getenv('USER'));


        // -----------------------------------------------------------------
        // string functions
        $this->fn(
            'cat',
            function () {
                return join('', func_get_args());
            }
        );
        $this->fn('trim');
        $this->fn('str_replace', 'str_replace');
        $this->fn(
            'sha1',
            function () {
                return sha1(join("", func_get_args()));
            }
        );
        $this->fn('ltrim');
        $this->fn('rtrim');
        $this->fn('sprintf');
        $this->fn(array('safename'), function ($fn) {
            return preg_replace('/[^a-z0-9]+/', '-', $fn);
        });

        // -----------------------------------------------------------------
        // I/O functions
        $this->fn('basename');
        $this->fn('dirname');
        $this->fn('is_file');
        $this->fn('is_dir');
        $this->fn('mtime', 'filemtime');
        $this->fn('atime', 'fileatime');
        $this->fn('ctime', 'filectime');
        $this->fn('escape', function ($value) {
            if (is_array($value)) {
                return array_map('escapeshellarg', $value);
            }
            return escapeshellarg($value);
        });
        $this->fn(
            'path',
            function () {
                return join(
                    "/",
                    array_map(
                        function ($el) {
                            return rtrim($el, "/");
                        },
                        array_filter(func_get_args())
                    )
                );
            }
        );

        // -----------------------------------------------------------------
        // array functions
        $this->fn('join', 'implode');
        $this->fn('keys', 'array_keys');
        $this->fn('values', 'array_values');
        $this->fn('range', function () {
            if (func_num_args() > 1) {
                return range(func_get_arg(1), func_get_arg(0));
            }
            return range(1, func_get_arg(0));
        });
        $this->fn('slice', 'array_slice');


        // -----------------------------------------------------------------
        // encoding / decoding
        $this->fn('json_encode', function ($v) {
            return json_encode($v, JSON_UNESCAPED_SLASHES);
        });
        $this->fn('json_decode');

        // -----------------------------------------------------------------
        // output functions
        $this->method(array('block'), function (Container $c, $messages, $style = null) {
            $io = new SymfonyStyle(new Input\ArrayInput(array()), $c->output);
            $messages = (array)$messages;
            if ($c->resolve('EXPLAIN')) {
                $messages = array_map(function ($m) { return trim($m) !== '' ? '# ' . str_replace("\n", "\n# ", $m) : ''; }, $messages);
            }
            if (count($messages) === 1 && strpos(current($messages), "\n") !== 0) {
                $messages = array("\n" . current($messages) . "\n");
            }
            $io->block($messages, null, $style);
        });

        // -----------------------------------------------------------------
        // other functions
        $this->fn('sh', array($this, 'helperExec'));
        $this->fn(
            'defaults',
            function(Container $c, $name, $env = null, $default = null) {
                if (null !== $env && null !== $bin = $c->resolve('globals.' . $name . '.' . $env )) {
                    return $bin;
                }
                return $default;
            },
            true
        );
        $this->fn('str', array($this, 'str'));
        $this->fn(
            array('url', 'host'),
            function ($url) {
                return parse_url($url, PHP_URL_HOST);
            }
        );
        $this->decl(array('now'), function () {
            return date('YmdHis');
        });

        $exitCode = self::ABORT_EXIT_CODE;
        $this->decl(array('abort'), function () use($exitCode) {
            return 'exit ' . $exitCode;
        });

        $this->fn('public_ip', function () {
            try {
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, 'https://api.ipify.org');
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 1);
                curl_setopt($ch, CURLOPT_HEADER, false);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                return curl_exec($ch);
            } catch (\Exception $e) {
                return null;
            } finally {
                if (isset($ch) && is_resource($ch)) {
                    curl_close($ch);
                }
            }
        });
    }


    /**
     * Return the raw context value at the specified path.
     *
     * @param array|string $path
     * @return mixed
     */
    public function get($path)
    {
        return $this->lookup($this->values, $path, true);
    }


    /**
     * Looks up a path in the specified context
     *
     * @param array $context
     * @param array $path
     * @param bool $require
     * @return string
     *
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     */
    public function lookup($context, $path, $require = false)
    {
        if (empty($path)) {
            throw new \InvalidArgumentException("Passed lookup path is empty.");
        }
        if (empty($context)) {
            throw new \InvalidArgumentException("Specified context is empty while resolving path " . json_encode($path));
        }
        return PropertyAccessor::getByPath($context, $this->path($path), $require);
    }

    /**
     * Resolve the specified path. If the resulting value is a Closure, it's assumed a declaration and therefore
     * executed
     *
     * @param array|string $id
     * @param bool $required
     * @return string
     *
     * @throws \RuntimeException
     * @throws CircularReferenceException
     */
    public function resolve($id, $required = false)
    {
        $id = $this->path($id);

        try {
            if (in_array($id, $this->resolutionStack)) {
                $path = array_map(
                    function ($a) {
                        return join('.', $a);
                    },
                    $this->resolutionStack
                );

                throw new CircularReferenceException(
                    sprintf(
                        "Circular reference detected: %s -> %s",
                        implode(' -> ', $path),
                        join('.', $id)
                    )
                );
            }

            array_push($this->resolutionStack, $id);
            $ret = $this->value($this->lookup($this->values, $id, $required));
            array_pop($this->resolutionStack);
            return $ret;
        } catch (\Exception $e) {
            if ($e instanceof CircularReferenceException) {
                throw $e;
            }
            throw new \RuntimeException("While resolving value " . join(".", $id), 0, $e);
        }
    }


    /**
     * Set the value at the specified path
     *
     * @param array|string $path
     * @param mixed $value
     * @return void
     *
     * @throws \UnexpectedValueException
     */
    public function set($path, $value)
    {
        PropertyAccessor::setByPath($this->values, $this->path($path), $value);
    }

    /**
     * This is useful for commands that need the shell regardless of the 'explain' value setting.
     *
     * @param string $cmd
     * @return mixed
     */
    public function helperExec($cmd)
    {
        if ($this->resolve('EXPLAIN')) {
            $this->output->writeln('<info># Task needs the following helper command:</info>');
            $this->output->writeln('<info># ' . str_replace("\n", "\\n", $cmd) . '</info>');
        }
        $ret = '';
        $this->executor->execute($cmd, $ret);
        return $ret;
    }

    /**
     * Wrapper for converting string paths to arrays.
     *
     * @param mixed $path
     * @return array
     *
     * @throws \InvalidArgumentException
     */
    private function path($path)
    {
        if (is_string($path)) {
            $path = explode('.', $path);
        }
        return $path;
    }


    /**
     * Set a function at the specified path.
     *
     * @param array|string $id
     * @param callable $callable
     * @param bool $needsContainer
     * @return void
     *
     * @throws \InvalidArgumentException
     */
    public function fn($id, $callable = null, $needsContainer = false)
    {
        if ($callable === null) {
            $callable = $id;
        }
        if (!is_callable($callable)) {
            throw new \InvalidArgumentException("Not callable");
        }
        $this->set($id, array($callable, $needsContainer));
    }


    /**
     * Creates a method-type function, i.e. the first parameter will always be the container.
     *
     * @param array $id
     * @param callable $callable
     * @return void
     */
    public function method($id, $callable)
    {
        $this->fn($id, $callable, true);
    }


    /**
     * Does a declaration, i.e., the first time the declaration is called, it's resulting value overwrites the
     * declaration.
     *
     * @param array|string $id
     * @param callable $callable
     * @return void
     *
     * @throws \InvalidArgumentException
     */
    public function decl($id, $callable)
    {
        if (!is_callable($callable)) {
            throw new \InvalidArgumentException("Passed declaration is not callable");
        }
        $this->set($id, function (Container $c) use($callable, $id) {
            Debug::enterScope(join('.', (array)$id));
            if (null !== ($value = call_user_func($callable, $c))) {
                $c->set($id, $value);
            }
            Debug::exitScope(join('.', (array)$id));
            return $value;
        });
    }

    /**
     * Output a notice
     *
     * @param string $message
     * @return void
     */
    public function notice($message)
    {
        $this->output->writeln("<comment>NOTICE: $message</comment>");
    }


    /**
     * Does an on-the-fly evaluation of the specified expression.
     * The compilation result will be stored in $code.
     *
     * @param string $expression
     * @param string &$code
     *
     * @return string
     */
    public function evaluate($expression, &$code = null)
    {
        $exprcompiler = new ScriptCompiler(new ExpressionParser(), new ExpressionTokenizer());

        $z = $this;
        $_value = null;
        $code = '$z->set(array(\'_\'), ' . $exprcompiler->compile($expression) . ');';
        eval($code);

        return $z->resolve(array('_'));
    }


    /**
     * Checks for existence of the specified path.
     *
     * @param string $id
     * @return string
     */
    public function has($id)
    {
        try {
            $existing = $this->get($id);
        } catch (\OutOfBoundsException $e) {
            return false;
        }
        return Util::typeOf($existing);
    }


    /**
     * Checks if a value is empty.
     *
     * @param mixed $path
     * @return bool
     */
    public function isEmpty($path)
    {
        try {
            $value = $this->get($path);
        } catch (\OutOfBoundsException $e) {
            return true;
        }
        return '' === $value || null === $value;
    }


    /**
     * Separate helper for calling a service as a function.
     *
     * @return mixed
     *
     * @throws \InvalidArgumentException
     */
    public function call()
    {
        $args = func_get_args();
        $service = array_shift($args);
        if (!is_array($service)) {
            throw new \RuntimeException(sprintf('Expected an array, got \'%s\'', is_object($service) ? get_class($service) : gettype($service)));
        }
        if (!is_callable($service[0])) {
            throw new \InvalidArgumentException("Can not use service '{$service[0]}' as a function, it is not callable");
        }

        // if the service needs the container, it is specified in the decl() call as the second param:
        if ($service[1]) {
            array_unshift($args, $this);
        }
        return call_user_func_array($service[0], $args);
    }

    /**
     * Helper to set prefix if the output if PrefixFormatter
     *
     * @param string $prefix
     * @return void
     */
    private function setOutputPrefix($prefix)
    {
        if (!($this->output->getFormatter() instanceof PrefixFormatter)) {
            return;
        }

        $this->output->getFormatter()->prefix = $prefix;
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
            $this->setOutputPrefix('');

            if ($this->resolve('DEBUG')) {
                $this->output->writeln('<comment># ' . join('::', Debug::$scope) . "</comment>");
            }

            if ($this->resolve('EXPLAIN')) {
                $cleanCmd = preg_replace(array("/\n *\\\\\n/", '/( +\\\\)+$/m'), array('', ' \\'), trim($cmd));
                if ($this->resolve('INTERACTIVE')) {
                    $this->notice('interactive shell:');
                    $line = '( /bin/bash -c \'' . $cleanCmd . '\' )';
                } elseif (($shell = $this->resolve(array('SHELL'))) !== $this->resolve(array('DEFAULT_SHELL'))) {
                    $line = 'echo ' . escapeshellarg($cleanCmd) . ' | ' . $shell;
                } else {
                    $line = $cleanCmd;
                }
                $this->output->writeln($line);
            } else {
                if ($this->resolve('DEBUG') && !$this->resolve('EXPLAIN')) {
                    $cmd = 'set -x; ' . $cmd;
                }
                $this->executor->execute($cmd);
            }
        }
    }

    /**
     * Execute a command. This is a wrapper for 'exec', so that a task prefixed with '@' can be passed as well.
     *
     * @param string $cmd
     * @return string|null
     */
    public function cmd($cmd)
    {
        $cmd = ltrim($cmd);
        if (substr($cmd, 0, 1) === '@') {
            return $this->resolve(array_merge(array('tasks'), explode('.', substr($cmd, 1))));
        } else {
            $this->exec($cmd);
            return null;
        }
    }


    /**
     * Returns the value representation of the requested variable
     *
     * @param string $value
     * @return string
     *
     * @throws \UnexpectedValueException
     */
    public function value($value)
    {
        if ($value instanceof \Closure) {
            $value = call_user_func($value, $this);
        }
        return $value;
    }


    /**
     * Convert the value to a string.
     *
     * @param mixed $value
     * @return string
     * @throws \UnexpectedValueException
     */
    public function str($value)
    {
        if (is_array($value)) {
            $allScalar = function ($a, $b) {
                return $a && is_scalar($b);
            };
            if (!array_reduce($value, $allScalar, true)) {
                throw new \UnexpectedValueException('Unexpected complex type ' . Util::toPhp($value));
            }
            return join(' ', $value);
        }
        return (string)$value;
    }


    /**
     * Register a plugin
     *
     * @param \Zicht\Tool\PluginInterface $plugin
     * @return void
     */
    public function addPlugin(PluginInterface $plugin)
    {
        $this->plugins[] = $plugin;
        $plugin->setContainer($this);
    }


    /**
     * Register a command
     *
     * @param \Symfony\Component\Console\Command\Command $command
     * @return void
     */
    public function addCommand(Command $command)
    {
        $this->commands[] = $command;
    }


    /**
     * Returns the registered commands
     *
     * @return Task[]
     */
    public function getCommands()
    {
        return $this->commands;
    }

    /**
     * Returns the values.
     *
     * @return array
     */
    public function getValues()
    {
        return $this->values;
    }


    /**
     * Can not be cloned
     *
     * @return void
     */
    private function __clone()
    {
    }


    /**
     * Check whether we're in debug mode or not.
     *
     * @return bool
     */
    public function isDebug()
    {
        return $this->get('DEBUG') === true;
    }


    /**
     * Push a var on a local stack by it's name.
     *
     * @param string $varName
     * @param string $tail
     * @return void
     */
    public function push($varName, $tail)
    {
        if (false === $this->has($varName)) {
            $this->set($varName, null);
        }
        if (!isset($this->varStack[json_encode($varName)])) {
            $this->varStack[json_encode($varName)] = array();
        }
        array_push($this->varStack[json_encode($varName)], $this->get($varName));
        $this->set($varName, $tail);
    }


    /**
     * Pop a var from a local var stack.
     *
     * @param string $varName
     * @return void
     */
    public function pop($varName)
    {
        $this->set($varName, array_pop($this->varStack[json_encode($varName)]));
    }
}
