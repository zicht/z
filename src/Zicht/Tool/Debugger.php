<?php
/**
 * @author    Philip Bergman <philip@zicht.nl>
 * @copyright Zicht Online <http://www.zicht.nl>
 */
namespace Zicht\Tool;

class Debugger
{
    private $container,
            $lastMessagesLength,
            $messageStatusMessage,
            $messageStatusDebugLevel;

    const // WRITE STATUSES
          DEBUG_NORMAL                 = 7,
          DEBUG_VERBOSE                = 46,
          DEBUG_EXPLAIN                = 92,
          DEBUG_NORMAL_OR_EXPLAIN      = 95,
          DEBUG_NORMAL_OR_VERBOSE      = 47,
          DEBUG_NORMAL_AND_VERBOSE     = 6,
          DEBUG_NORMAL_AND_EXPLAIN     = 4,
          DEBUG_VERBOSE_AND_EXPLAIN    = 12,
          DEBUG_VERBOSE_OR_EXPLAIN     = 126,
          DEBUG_ALL                    = 127,
          // STATUS STATUSES
          DEBUG_MESSAGE_STATUS_OK      = 0,
          DEBUG_MESSAGE_STATUS_WARNING = 1,
          DEBUG_MESSAGE_STATUS_ERROR   = 2;

    /**
     * setting container
     *
     * @param Container\Container $container
     */
    public function __construct( \Zicht\Tool\Container\Container $container ){ //OutputInterface $output, $verbose = false, $explain = false){
        $this->container = $container;
    }

    /**
     * check if we are allowed to wright
     *
     * @param $type
     * @return bool
     */
    public function canWrite($type){

        $return = false;

        switch($type){
            case self::DEBUG_ALL:
                $return = true;
                break;
            case self::DEBUG_NORMAL:
                $return = ( $this->container->has('verbose') === false &&  $this->container->has('explain') === false );
                break;
            case self::DEBUG_VERBOSE:
                $return = ( $this->container->has('verbose') === true );
                break;
            case self::DEBUG_EXPLAIN:
                $return = ( $this->container->has('explain') === true );
                break;
            case self::DEBUG_NORMAL_OR_EXPLAIN:
                $return = ( $this->container->has('verbose') === false );
                break;
            case self::DEBUG_NORMAL_OR_VERBOSE:
                $return = ( $this->container->has('explain') === false );
                break;
            case self::DEBUG_NORMAL_AND_VERBOSE:
                $return = ( $this->container->has('verbose') === true  &&  $this->container->has('explain') === false );
                break;
            case self::DEBUG_NORMAL_AND_EXPLAIN:
                $return = ( $this->container->has('verbose') === false &&  $this->container->has('explain') === true );
                break;
            case self::DEBUG_VERBOSE_AND_EXPLAIN:
                $return = ( $this->container->has('verbose') === true  &&  $this->container->has('explain') === true );
                break;
            case self::DEBUG_VERBOSE_OR_EXPLAIN:
                $return = ( $this->container->has('verbose') === true  ||  $this->container->has('explain') === true );
                break;
        }

        return $return;
    }

    /**
     * will write line if matches debug level
     *
     * @param string    $message    the message
     * @param int       $debugLevel debug type
     * @param bool      $rewrite    rewrite line
     */
    public function write($message, $debugLevel = self::DEBUG_NORMAL, $rewrite = false){

        if ($this->canWrite($debugLevel) === true) {

            if($rewrite === true){

                $length = $this->strlen($message);

                // append whitespace to match the last line's length
                if (null !== $this->lastMessagesLength && $this->lastMessagesLength > $length) {
                    $message = str_pad($message, $this->lastMessagesLength, "\x20", STR_PAD_RIGHT);
                }

                // carriage return
                $this->container->output->write("\x0D");
                $this->container->output->write($message);

            }else{

                $this->container->output->write($message);

            }

            $this->lastMessagesLength = $this->strlen($message);
        }
    }

    /**
     * writes line with new line on the end if debug level matches current level
     *
     * @param $message
     * @param int $debugLevel
     */
    public function writeln($message, $debugLevel = self::DEBUG_NORMAL){

        if ($this->canWrite($debugLevel) === true) {
            $this->container->output->writeln($message);
        }
    }

    /**
     * setup for status progress message
     *
     * @param $message
     * @param int $debugLevel
     */
    public function messageProgressStart($message, $debugLevel = self::DEBUG_NORMAL){
        $this->messageStatusMessage    = $message;
        $this->messageStatusDebugLevel = $debugLevel;

        $this->write(
            sprintf("[%-7s] %s", "", $message),
            $debugLevel
        );
    }

    /**
     * writes finished status of message status
     *
     * @param int $status
     * @param string $suffix
     */
    public function messageProgressFinished($status = self::DEBUG_MESSAGE_STATUS_OK, $suffix = "\n"){


        $messageMarkup = array(
            self::DEBUG_MESSAGE_STATUS_OK => array(
                'markup'  => 'info',
                'message' =>  'success'
            ),
            self::DEBUG_MESSAGE_STATUS_WARNING => array(
                'markup'  => 'comment',
                'message' => 'notice'
            ),
            self::DEBUG_MESSAGE_STATUS_ERROR => array(
                'markup'  => 'fg=red',
                'message' => 'error'
            ),
        );

        $this->write(
            sprintf(
                "[<%s>%-7s</%s>] %s%s",
                $messageMarkup[$status]['markup'],
                $messageMarkup[$status]['message'],
                $messageMarkup[$status]['markup'],
                $this->messageStatusMessage,
                $suffix
            ),
            $this->messageStatusDebugLevel,
            true
        );
    }


    /**
     *
     * Returns the length of a string, using mb_strlen if it is available.
     *
     * @param string $string The string to check its length
     *
     * @return integer The length of the string
     */

    private function strlen($string){

        if (!function_exists('mb_strlen')) {
            return strlen($string);
        }

        if (false === $encoding = mb_detect_encoding($string)) {
            return strlen($string);
        }

        return mb_strlen($string, $encoding);
    }

}