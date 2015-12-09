<?php
namespace Zicht\Tool\Command\Descriptor;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Descriptor\TextDescriptor as BaseDescriptor;
use Symfony\Component\Console\Descriptor\ApplicationDescription;

/**
 */
class TextDescriptor extends BaseDescriptor
{
    /**
     * @param \Symfony\Component\Console\Input\InputOption $option
     * @return bool
     */
    public function isHiddenOption(InputOption $option)
    {
        return in_array(
            $option->getName(),
            array('help', 'version', 'verbose', 'quiet', 'explain', 'force', 'plugin', 'debug')
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function describeCommand(Command $command, array $options = array())
    {
        list($public, $internal) = $this->splitDefinition($command->getNativeDefinition());

        $synopsis = new Command($command->getName());
        $synopsis->setDefinition($public);

        $this->writeText('<comment>Usage:</comment>', $options);
        $this->writeText("\n");
        $this->writeText(' '.$synopsis->getSynopsis(), $options);
        $this->writeText("\n");

        if (count($command->getAliases()) > 0) {
            $this->writeText("\n");
            $this->writeText('<comment>Aliases:</comment> <info>'.implode(', ', $command->getAliases()).'</info>', $options);
        }

        $this->writeText("\n");
        $this->describeInputDefinition($public, $options);
        if (count($internal->getOptions())) {
            $this->writeText("\n\n" . '<comment>Global options:</comment>' . "\n");
            $this->writeText(' Following global options are available for all task commands:');
            $this->writeText(' ' . join(', ', array_map(function($o) { return $o->getName(); }, $internal->getOptions())));
            $this->writeText(' Read the application help for more info' . "\n");
        }
        $this->writeText("\n");

        if ($help = $command->getProcessedHelp()) {
            $this->writeText('<comment>Help:</comment>', $options);
            $this->writeText("\n");
            $this->writeText(' '.str_replace("\n", "\n ", $help), $options);
            $this->writeText("\n");
        }

        $this->writeText("\n");
    }

    /**
     * Splits the definition in an internal and public one, the latter representing whatever is shown to the user,
     * the former representing the options that are hidden.
     *
     * @param \Symfony\Component\Console\Input\InputDefinition $definition
     * @return array
     */
    public function splitDefinition(InputDefinition $definition)
    {
        /** @var InputDefinition[] $ret */
        $ret = array(
            new InputDefinition(),
            new InputDefinition()
        );

        foreach ($definition->getArguments() as $arg) {
            $ret[0]->addArgument($arg);
            $ret[1]->addArgument($arg);
        }
        foreach ($definition->getOptions() as $opt) {
            $ret[$this->isHiddenOption($opt) ? 1 : 0]->addOption($opt);
        }
        return $ret;
    }


    /**
     * @param \Symfony\Component\Console\Application $application
     * @param array $options
     */
    protected function describeApplication(Application $application, array $options = array())
    {
        $describedNamespace = isset($options['namespace']) ? $options['namespace'] : null;
        $description = new ApplicationDescription($application, $describedNamespace);

        $width = $this->getColumnWidth($description->getCommands());

        $this->writeText($application->getHelp(), $options);
        $this->writeText("\n\n");

        if ($describedNamespace) {
            $this->writeText(sprintf("<comment>Available commands for the \"%s\" namespace:</comment>", $describedNamespace), $options);
        } else {
            $this->writeText('<comment>Available commands:</comment>', $options);
        }

        // add commands by namespace
        foreach ($description->getNamespaces() as $namespace) {
            if (!$describedNamespace && ApplicationDescription::GLOBAL_NAMESPACE !== $namespace['id']) {
                $this->writeText("\n");
                $this->writeText('<comment>'.$namespace['id'].'</comment>', $options);
            }

            foreach ($namespace['commands'] as $name) {
                $this->writeText("\n");
                $this->writeText(sprintf("  <info>%-${width}s</info> %s", $name, $description->getCommand($name)->getDescription()), $options);
            }
        }

        $this->writeText("\n");
    }

    /**
     * Because for some inapparent reason, the parent's implementation is private.
     *
     * @param string $content
     * @param array $options
     */
    private function writeText($content, array $options = array())
    {
        $this->write(
            isset($options['raw_text']) && $options['raw_text'] ? strip_tags($content) : $content,
            isset($options['raw_output']) ? !$options['raw_output'] : true
        );
    }

    /**
     * Because for some inapparent reason, the parent's implementation is private.
     */
    private function getColumnWidth(array $commands)
    {
        $width = 0;
        foreach ($commands as $command) {
            $width = strlen($command->getName()) > $width ? strlen($command->getName()) : $width;
        }

        return $width + 2;
    }
}