<?php
/**
 * For licensing information, please see the LICENSE file accompanied with this file.
 *
 * @author Gerard van Helden <drm@melp.nl>
 * @copyright 2012 Gerard van Helden <http://melp.nl>
 */

namespace Zicht\Tool\Packager\Cmd;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Zicht\Tool\Packager\Packager;

/**
 * Command for building a package.
 */
class BuildCommand extends Command
{
    /**
     * @{inheritDoc}
     */
    protected function configure()
    {
        parent::configure();
        $this
            ->setName('build')
            ->addArgument('file', InputArgument::OPTIONAL, 'File name to write to', 'z')
            ->addOption('force', 'f', InputOption::VALUE_NONE, 'Force the file to be written')
            ->addOption('app-version', '', InputOption::VALUE_REQUIRED, 'Version string for the build')
            ->addOption('app-name', '', InputOption::VALUE_REQUIRED, 'Name of the application')
            ->addOption(
                'config-filename', '', InputOption::VALUE_REQUIRED,
                'The config file name for the app to use (typically z.yml)'
            )
            ->addOption(
                'static', '', InputOption::VALUE_REQUIRED,
                "Create a static build using the specified config file"
            )
        ;
    }

    /**
     * @{inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $t = microtime(true);
        $packager = new Packager(__DIR__ . '/../../../../../', array_filter($input->getOptions()));

        $result = $packager->package($input->getArgument('file'), $input->getOption('force'));

        if ($output->getVerbosity() > OutputInterface::VERBOSITY_NORMAL) {
            $output->writeln(sprintf("Built {$result} in %.2f seconds", microtime(true) - $t));
        }
    }
}
