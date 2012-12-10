<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */
namespace Zicht\Tool\Versioning\Svn;

use Zicht\Tool\Versioning\VersioningInterface;

use AppendIterator;
use Melp\Vcs\RemoteSvn;
use Zicht\Tool\Versioning\Version;

class Versioning implements VersioningInterface
{
    // TODO inject dependency
    function __construct($remote) {
        $this->svn = new RemoteSvn(new \Melp\Vcs\Svn\CliAdapter());
        $this->svn->init($remote);
    }


    function export($version, $targetPath) {
        $this->svn->checkout($version);
        $this->svn->export($targetPath);
    }


    /**
     * @return Version[]
     */
    function listVersions() {
        $ret = array();
        foreach ($this->svn->ls('tags') as $name => $svnInfo) {
            $ret[]= new Version(Version::TAG, $name, $svnInfo['commit']);
        }
        foreach ($this->svn->ls('branches') as $name => $svnInfo) {
            $ret[]= new Version(Version::BRANCH, $name, $svnInfo['commit']);
        }
        return new \ArrayIterator($ret);
    }


    function checkout($targetPath) {
        // TODO: Implement checkout() method.
    }

    function createTag($name) {
        // TODO: Implement createTag() method.
    }

    function createBranch($name, $src = null) {
        // TODO: Implement createBranch() method.
    }

}