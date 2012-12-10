<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */
namespace Zicht\Tool\Versioning;

interface VersioningInterface
{
    function export($version, $targetPath);

    /**
     * @return Version[]
     */
    function listVersions();
    function checkout($targetPath);
    function createTag($name);
    function createBranch($name, $src = null);
}