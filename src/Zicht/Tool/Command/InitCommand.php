<?php
/**
 * For licensing information, please see the LICENSE file accompanied with this file.
 *
 * @author Gerard van Helden <drm@melp.nl>
 * @copyright 2012 Gerard van Helden <http://melp.nl>
 */

namespace Zicht\Tool\Command;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command;

class InitCommand extends BaseCommand
{
    protected function configure()
    {
        $this
            ->setName('z:init')
            ->setHelp('Initialize a z-file in the current working directory')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (!$input->isInteractive()) {
            $output->writeln('<error>This command can not be run in non-interactive mode</error>');
            return 1;
        }

        /** @var $helper \Symfony\Component\Console\Helper\DialogHelper */
        $helper = $this->getHelperSet()->get('dialog');
        $ask = function($q, $default = null) use($helper, $output) {
            return $helper->ask($output, $q . ($default ? sprintf(' [<info>%s</info>]', $default) : '') . ': ', $default);
        };
        $yn = function($q) use($helper, $output) {
            return $helper->askConfirmation($output, $q . ' [y/N] ', false);
        };

        $config = array();
        $config['vcs']['url'] = preg_replace('!/(trunk|branches/[^/]+)/?!', '', trim(shell_exec('svn info | grep URL | awk \'{print $2}\'')));
        $config['vcs']['url'] = $ask('VCS url', $config['vcs']['url']);

        while ($yn('Add an environment?') == 'y') {
            $cfg = array();
            foreach (
                array(
                    'name'      => 'Environment name',
                    'url'       => 'URL',
                    'ssh'       => 'SSH (user@host or config name)',
                    'db'        => 'Database',
                    'root'      => 'Deployment root',
                    'web'       => 'Web root (relative to deployment root)'
                ) as $key => $q)
            {
                $cfg[$key] = $ask($q);
            }
            $config['env'][$cfg['name']] = $cfg;
            unset($config['env'][$cfg['name']]['name']);

            $output->writeln("Config is now:");
            $output->writeln("----");
            $output->writeln($ymlConfig = \Symfony\Component\Yaml\Yaml::dump($config, 4, 4));
            $output->writeln("----");
        }

        file_put_contents('z.yml', $ymlConfig);
        $output->writeln(realpath('z.yml') .  ' written');
    }
}