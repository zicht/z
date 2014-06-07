<?php
namespace Zicht\Tool;

use \Symfony\Component\Console\Helper\DialogHelper;
use Zicht\Tool\Container\ExecutionAbortedException;
use \Symfony\Component\Console\Output\OutputInterface;
use \Symfony\Component\Console\Input\InputInterface;

class ErrorHandler
{
    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    public function __construct($input, $output)
    {
        $this->input = $input;
        $this->output = $output;
        $this->repeating = array();
        $this->dialog = new DialogHelper();
        $this->continueAlways = false;
    }

    /**
     * Handler implementation which allows for some user interaction based on emitted user errors.
     *
     * @param int $err
     * @param string $errstr
     * @return void
     *
     * @throws Container\ExecutionAbortedException
     */
    public function __invoke($err, $errstr)
    {
        if (in_array($errstr, $this->repeating)) {
            return;
        }
        $this->repeating[]= $errstr;
        if (
            error_reporting() & E_USER_DEPRECATED
            || error_reporting() & E_USER_NOTICE
            || error_reporting() & E_USER_WARNING
        ) {
            switch ($err) {
                case E_USER_WARNING:
                    fprintf(STDERR, $this->output->getFormatter()->format("<comment>[WARNING]</comment>   $errstr\n"));

                    if (!$this->continueAlways) {
                        do {
                            if ($this->input->isInteractive()) {
                                $answer = $this->dialog->ask($this->output, "Continue anyway? (y)es, (n)o, (a)lways ", false);
                            } else {
                                $answer = 'n';
                            }
                        } while (!in_array(strtolower($answer), array('y', 'n', 'a')));

                        if ($answer === 'n') {
                            throw new ExecutionAbortedException("Aborted by user request");
                        } elseif ($answer === 'a') {
                            $continueAlways = true;
                        }
                    }


                    break;
                case E_USER_NOTICE:
                    if ($this->output->getVerbosity() >= OutputInterface::VERBOSITY_NORMAL) {
                        fprintf(STDERR, $this->output->getFormatter()->format("<comment>[NOTICE]</comment>   $errstr\n"));
                    }
                    break;
                case E_USER_DEPRECATED:
                    if ($this->output->getVerbosity() >= OutputInterface::VERBOSITY_NORMAL) {
                        fprintf(STDERR, $this->output->getFormatter()->format("<comment>[DEPRECATED]</comment>   $errstr\n"));
                    }
                    break;
            }
        }
    }
}