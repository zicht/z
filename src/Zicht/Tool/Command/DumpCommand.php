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
class DumpCommand extends BaseCommand
{
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
            ->addArgument('path', InputArgument::OPTIONAL, 'Dump the specified path in the config')
            ->setHelp('Dumps container and/or configuration information')
            ->addOption('verify', '', \Symfony\Component\Console\Input\InputOption::VALUE_NONE, 'Verifies the code (lint through php -l)')
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
        if ($path = $input->getArgument('path')) {
            $ptr = $this->container['__config'];
            $parts = explode('.', $path);
            while ($key = array_shift($parts)) {
                if (isset($ptr[$key])) {
                    $ptr = $ptr[$key];
                } else {
                    throw new \InvalidArgumentException("Key {$key} is not defined");
                }
            }
            $slice = array($path => $ptr);
            $output->writeln(Yaml::dump($slice, 5, 4));
        } else {
            $output->writeln(Yaml::dump($this->container['__config'], 5, 4));
            if ($output->getVerbosity() > 1) {
                $output->writeln($this->container['__definition'], OutputInterface::OUTPUT_RAW);
            }
        }
        if ($input->getOption('verify')) {
            $f = tempnam(sys_get_temp_dir(), 'z');
            file_put_contents($f, '<?php' . PHP_EOL . $this->container['__definition']);
            passthru('php -l ' . $f);
        }
    }
}