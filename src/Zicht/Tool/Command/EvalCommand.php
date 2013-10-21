<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */
namespace Zicht\Tool\Command;

use \Symfony\Component\Console\Input;
use \Symfony\Component\Console\Output\OutputInterface;
use \Symfony\Component\Yaml\Yaml;

class EvalCommand extends BaseCommand
{
    protected function configure()
    {
        $this
            ->setName('z:eval')
            ->addArgument('expression', Input\InputArgument::REQUIRED, 'The expression to evaluate')
            ->addOption('format', '', Input\InputOption::VALUE_REQUIRED, 'Format to output', 'json')
            ->setHelp('Available output formats are \'json\', \'yml\' or \'php\'')
            ->setDescription('Evaluates an expression within the scope of the container.')
        ;
    }


    protected function execute(Input\InputInterface $input, OutputInterface $output)
    {
        $expr   = $input->getArgument('expression');
        $result = $this->container->evaluate($expr, $code);

        if ($output->getVerbosity() > OutputInterface::VERBOSITY_NORMAL) {
            $output->writeln($code);
        }

        switch ($input->getOption('format')) {
            case 'json':
                $output->writeln(json_encode($result));
                break;
            case 'yml':
            case 'yaml':
                $output->writeln(Yaml::dump($result));
                break;
            case 'php':
                $output->writeln(var_export($result, true));
                break;
            default:
                throw new \InvalidArgumentException("Unsupported output format {$input->getOption('format')}");
        }
    }
}