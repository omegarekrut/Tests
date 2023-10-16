<?php

namespace Tests\Unit\Module\TacticianQueueInterop;

use App\Module\TacticianQueueInterop\Handler\ImmediatelyExecuteCommandFromQueueHandler;
use App\Module\TacticianQueueInterop\QueueCommand;
use Tests\Unit\Mock\CommandBusMock;
use Tests\Unit\TestCase;

class ImmediatelyExecuteCommandFromQueueHandlerTest extends TestCase
{
    public function testCommandMustBeHandledImmediately(): void
    {
        $expectedCommand = new \stdClass();
        $expectedCommand->expectedCommandField = 'command field';

        $commandBus = new CommandBusMock();
        $sendCommandToQueueHandlerTest = new ImmediatelyExecuteCommandFromQueueHandler($commandBus);
        $sendCommandToQueueHandlerTest->handle(new QueueCommand($expectedCommand));

        $handledCommand = $commandBus->getLastHandledCommand();

        $this->assertNotEmpty($handledCommand);
        $this->assertEquals((object) $expectedCommand, $handledCommand);
    }
}
