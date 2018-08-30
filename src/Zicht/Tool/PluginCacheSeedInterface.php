<?php
/**
 * @author    Philip Bergman <philip@zicht.nl>
 * @copyright Zicht Online <http://www.zicht.nl>
 */
namespace Zicht\Tool;

/**
 * Interface PluginCacheSeedInterface
 *
 * An interface that gives plugins the capability to
 * dynamically force to recompile of the container.
 *
 * @package Zicht\Tool
 */
interface PluginCacheSeedInterface
{

    /**
     * @param array $config
     * @return string
     */
    public function getCacheSeed(array $config = []);
}
