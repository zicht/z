<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */
namespace Zicht\Tool\Versioning;

interface VersioningInterface
{
    public function export($version, $targetPath);

    /**
     * @return Version[]
     */
    public function listVersions();
    public function checkout($targetPath);
    public function createTag($name);
    public function createBranch($name, $src = null);
}
