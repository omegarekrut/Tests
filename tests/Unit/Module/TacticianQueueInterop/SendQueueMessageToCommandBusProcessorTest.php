<?php

namespace Tests\Unit\Module\TacticianQueueInterop;

use App\Module\TacticianQueueInterop\Handler\SendQueueMessageToCommandBusProcessor;
use Enqueue\Null\NullMessage;
use Interop\Queue\PsrContext;
use Tests\Unit\Mock\CommandBusMock;
use Tests\Unit\TestCase;

class SendQueueMessageToCommandBusProcessorTest extends TestCase
{
    /** @var CommandBusMock */
    private $commandBusMock;
    /** @var SendQueueMessageToCommandBusProcessor */
    private $sendQueueMessageToCommandBusProcessor;

    protected function setUp(): void
    {
        $this->commandBusMock = new CommandBusMock();
        $this->sendQueueMessageToCommandBusProcessor = new SendQueueMessageToCommandBusProcessor($this->commandBusMock);
    }

    public function testCommandMustBeObtainsFromMessageBodyAndSendToCommandBus(): void
    {
        $expectedCommand = new \stdClass();
        $expectedCommand->expectedCommandField = 'command field';

        $psrMessage = new NullMessage(serialize($expectedCommand));
        $this->sendQueueMessageToCommandBusProcessor->process($psrMessage, $this->createMock(PsrContext::class));

        $sentCommand = $this->commandBusMock->getLastHandledCommand();

        $this->assertNotEmpty($sentCommand);
        $this->assertEquals($expectedCommand, $sentCommand);
    }
}
