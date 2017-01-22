<?php

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\PyStringNode;

/**
 * Defines application features from the specific context.
 */
class FeatureContext implements Context
{
    private $testDir;
    private $zBinary;
    private $zBinaryPath;
    private $packageBinary;
    private $response;


    /**
     * Initializes context.
     *
     * Every scenario gets its own context instance.
     * You can also pass arbitrary arguments to the
     * context constructor through behat.yml.
     */
    public function __construct()
    {
        $this->testDir = __DIR__ . '/../tmp';
        $this->zBinary = escapeshellarg(__DIR__ . '/../../../bin/z');
        $this->zBinaryPath = realpath(__DIR__ . '/../../../bin/z');
        $this->packageBinary = escapeshellarg(__DIR__ . '/../../../bin/package.php');

        putenv('ZPATH=.');
        putenv('ZFILE=z.yml');
    }


    /**
     * @Given /^I am in (?P<type>a|the) test directory$/
     */
    public function iAmInATestDirectory($type)
    {
        if ($type == "a") {
            if (!is_dir($this->testDir)) {
                mkdir($this->testDir);
            }
            chdir($this->testDir);
            shell_exec('rm -r *');
        } else {
            chdir($this->testDir);
        }
    }


    /**
     * @Given /^there is (?:a )?file "(?P<file>[^"]+)"$/
     */
    public function thereIsFile($file, PyStringNode $string)
    {
        $dir = dirname($file);
        if ($dir && !is_dir($dir)) {
            mkdir($dir);
        }

        file_put_contents($file, $string->getRaw());
    }


    /**
     * @Given /^there is an executable file "([^"]*)" with a shebang pointing to Z$/
     */
    public function thereIsAFileWithAShebangPointingToZ($file, PyStringNode $string)
    {
        $this->thereIsFile($file, $string);
        // this is necessary when the binary is inside a path that contains spaces.
        // A shebang path cannot contain spaces... whoa.
        @unlink("/tmp/z-interpreter");
        symlink($this->zBinaryPath, "/tmp/z-interpreter");
        file_put_contents($file, "#!/tmp/z-interpreter -\n\n" . file_get_contents($file));
        chmod($file, 0755);
    }


    /**
     * @When /^I run "z ([^"]*)"$/
     */
    public function iRunZ($cmd)
    {
        $this->response = shell_exec($this->zBinary . ' ' . $cmd . ' 2>&1');
    }

    /**
     * @When /^I run "package ([^"]*)"$/
     */
    public function iRunPackage($cmd)
    {
        $this->response = shell_exec('php ' . $this->packageBinary . ' ' . $cmd . ' 2>&1');
    }

    /**
     * @When /^I run ".\/([^"]*)"$/
     */
    public function iRunALocalScript($cmd)
    {
        $this->response = shell_exec('./' . $cmd . ' 2>&1');
    }

    /**
     * @Then /^I should (?P<negate>not )?see text matching "(?P<pattern>[^"]*)"$/
     */
    public function iShouldSeeTextMatching($pattern, $negate = false)
    {
        $res = preg_match($pattern, $this->response);
        if ((bool)$res == (bool)$negate) {
            throw new UnexpectedValueException(
                "Pattern {$pattern} was" . ($negate ? "" : " not") . " found in program response:\n{$this->response}"
            );
        }
    }
}
