<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Tool\Command;

use \Symfony\Component\DependencyInjection\ContainerInterface;
use \Symfony\Component\Console\Input\InputInterface;
use \Symfony\Component\Console\Output\OutputInterface;
use \Symfony\Component\Yaml\Yaml;

/**
 * Command to initialize a z file
 */
class InitCommand extends BaseCommand
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
            ->setName('z:init')
            ->setHelp('Initialize a z-file in the current working directory')
        ;
    }

    /**
     * Executes the command
     *
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (!$input->isInteractive()) {
            $output->writeln('<error>This command can not be run in non-interactive mode</error>');
            return 1;
        }

        /** @var $helper \Symfony\Component\Console\Helper\DialogHelper */
        $config = array();
        $config['vcs']['url'] = preg_replace(
            '!/(trunk|branches/[^/]+)$/?!',
            '',
            trim(shell_exec('svn info | grep URL | awk \'{print $2}\''))
        );
        $config['vcs']['url'] = $this->container['ask']('VCS url', $config['vcs']['url']);

        $settings = array(
            'name'  => 'Environment name',
            'url'   => 'URL',
            'ssh'   => 'SSH (user@host or config name)',
            'db'    => 'Database',
            'root'  => 'Deployment root',
            'web'   => 'Web root (relative to deployment root)'
        );

        $ymlConfig = Yaml::dump($config, 4, 4);

        while ($this->container['confirm']('Add an environment?') == 'y') {
            $cfg = array();
            foreach ($settings as $key => $q) {
                $cfg[$key] = $this->container['ask']($q);
            }
            $config['env'][$cfg['name']] = $cfg;
            unset($config['env'][$cfg['name']]['name']);

            $output->writeln("Config is now:");
            $output->writeln("----");
            $output->writeln($ymlConfig = Yaml::dump($config, 4, 4));
            $output->writeln("----");
        }

        file_put_contents('z.yml', $ymlConfig);
        $output->writeln(realpath('z.yml') .  ' written');
        return 0;
    }
}