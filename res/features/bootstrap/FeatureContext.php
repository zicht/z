<?php

use Behat\Behat\Context\ClosuredContextInterface,
    Behat\Behat\Context\TranslatedContextInterface,
    Behat\Behat\Context\BehatContext,
    Behat\Behat\Exception\PendingException;
use Behat\Gherkin\Node\PyStringNode,
    Behat\Gherkin\Node\TableNode;

//
// Require 3rd-party libraries here:
//
//   require_once 'PHPUnit/Autoload.php';
//   require_once 'PHPUnit/Framework/Assert/Functions.php';
//

/**
 * Features context.
 */
class FeatureContext extends BehatContext
{

    /**
     * Initializes context.
     * Every scenario gets it's own context object.
     *
     * @param   array   $parameters     context parameters (set them up through behat.yml)
     */
    public function __construct(array $parameters)
    {
        $this->testDir = __DIR__ . '/../tmp';
        $this->zBinary = escapeshellarg(__DIR__ . '/../../../bin/z');
    }


    /**
     * @Given /^I am in a test directory$/
     */
    public function iAmInATestDirectory()
    {
        if (!is_dir($this->testDir)) {
            mkdir($this->testDir);
        }
        chdir($this->testDir);
    }


    /**
     * @Given /^there is (?:a )?file "(?P<file>[^"]+)"$/
     */
    public function thereIsFile($file, $string)
    {
        $dir = dirname($file);
        if ($dir && !is_dir($dir)) {
            mkdir($dir);
        }

        file_put_contents($file, join("\n", $string->getLines()));
    }



    /**
     * @When /^I run "z ([^"]*)"$/
     */
    public function iRun($cmd)
    {
        $this->response = shell_exec($this->zBinary . ' ' . $cmd . ' 2>&1');
    }


    /**
     * @Then /^I should (?P<negate>not )?see text matching "(?P<pattern>[^"]*)"$/
     */
    public function iShouldSeeTextMatching($pattern, $negate = false)
    {
        $res = preg_match($pattern, $this->response);
        if ((bool)$res == (bool)$negate) {
            throw new UnexpectedValueException("Pattern {$pattern} was not found in program response:\n{$this->response}");
        }
    }
}
