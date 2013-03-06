<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */
namespace Zicht\Tool\Command;

use \Symfony\Component\DependencyInjection\ContainerInterface;
use \Symfony\Component\Console\Input\InputInterface;
use \Symfony\Component\Console\Input\InputArgument;
use \Symfony\Component\Console\Output\OutputInterface;
use \Symfony\Component\Console\Command\Command;
use \Symfony\Component\Yaml\Yaml;

/**
 * Dumps the container
 */
class DumpCommand extends Command
{
    public function __construct($containerNode, $configTree)
    {
        parent::__construct();
        $this->containerNode = $containerNode;
        $this->configTree = $configTree;
    }


    /**
     * Configures the command
     *
     * @return void
     */
    protected function configure()
    {
        parent::configure();

        $this
            ->setName('z:dump')
            ->setHelp('Dumps container and/or configuration information')
            ->setDescription('Dumps container and/or configuration information')
        ;
    }


    /**
     * Executes the command
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln(Yaml::dump($this->configTree, 5, 4));
        if ($output->getVerbosity() > 1) {
            $buffer = new \Zicht\Tool\Script\Buffer();
            $this->containerNode->compile($buffer);
            $result = $buffer->getResult();

            $output->writeln(
                preg_replace_callback('/^/m', function() { static $i = 1; return sprintf("%3d. ", $i ++); }, $result),
                OutputInterface::OUTPUT_RAW
            );
        }
    }
}