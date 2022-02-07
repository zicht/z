<?php
/**
 * @copyright Zicht Online <https://zicht.nl>
 */

namespace Zicht\Tool;

use Symfony\Component\Console\Helper\DialogHelper;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Zicht\Tool\Container\ExecutionAbortedException;

/**
 * Wrapper class for handling errors
 */
class ErrorHandler
{
    private $input;
    private $output;
    private $repeating = array();
    private $continueAlways = false;

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    public function __construct($input, $output)
    {
        $this->input = $input;
        $this->output = $output;
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
        $this->repeating[] = $errstr;
        if (
            error_reporting() & E_USER_DEPRECATED
            || error_reporting() & E_USER_NOTICE
            || error_reporting() & E_USER_WARNING
            || error_reporting() & E_RECOVERABLE_ERROR
        ) {
            switch ($err) {
                case E_USER_WARNING:
                    fprintf(STDERR, $this->output->getFormatter()->format("<comment>[WARNING]</comment>   $errstr\n"));

                    if (!$this->continueAlways) {
                        do {
                            if ($this->input->isInteractive()) {
                                $answer = $this->askConfirmation('Continue anyway? (y)es, (n)o, (a)lways ', false);
                            } else {
                                $answer = 'n';
                            }
                        } while (!in_array(strtolower($answer), array('y', 'n', 'a')));

                        if ($answer === 'n') {
                            throw new ExecutionAbortedException("Aborted by user request");
                        } elseif ($answer === 'a') {
                            $this->continueAlways = true;
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
                case E_RECOVERABLE_ERROR:
                    throw new \ErrorException($errstr);
            }
        }
    }

    private function askConfirmation($question, $default)
    {
        if (class_exists(QuestionHelper::class)) {
            $helper = new QuestionHelper();
            $confirmationQuestion = new ConfirmationQuestion($question, $default);
            return $helper->ask($this->input, $this->output, $confirmationQuestion);
        }

        // Sf < 3.3
        $helper = new DialogHelper();
        return $helper->ask($this->output, $question, $default);
    }
}
