<?php

namespace Tests\Functional\Command;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Tests\Functional\TestCase as FunctionalTestCase;

abstract class TestCase extends FunctionalTestCase
{
    protected function getConsoleApplicationWithCommand(Command $command): Application
    {
        $application = new Application($this->getKernel());
        $application->add($command);

        return $application;
    }

    protected function getCommandTester(Command $command): CommandTester
    {
        return new CommandTester($command);
    }
}
