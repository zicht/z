<?php
/**
 * @author    Philip Bergman <philip@zicht.nl>
 * @copyright Zicht Online <http://www.zicht.nl>
 */
namespace Zicht\Tool;

/**
 * Interface PluginTaskListenerInterface
 *
 * Interface to give a plugin the possibility to adjust a TaskCommand on runtime.
 *
 * @package Zicht\Tool
 */
interface PluginTaskListenerInterface
{
    /**
     * Return a array of task to listen for, this should be a array with the task name as key
     * and method as value. The method will be called with the TaskCommand as first argument.
     *
     * for example:
     *
     *  return array(
     *      'deploy' => 'doThis'
     *  )
     *
     * That will run method doThis(Symfony\Component\Console\Command\Command $c) when the
     * application is set for the deploy task (when the command is added to tha main Application).
     *
     * @return array
     */
    public function getTaskListeners();
}