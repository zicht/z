<?php

namespace Zicht\Tool\Container;

use Symfony\Component\Console\Output\OutputInterface;

interface VerboseException
{
    public function output(OutputInterface $o);
}