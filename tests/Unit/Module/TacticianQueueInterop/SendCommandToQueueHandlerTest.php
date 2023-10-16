<?php

namespace Tests\Unit\Module\TacticianQueueInterop;

use App\Module\TacticianQueueInterop\QueueCommand;
use App\Module\TacticianQueueInterop\Handler\SendCommandToQueueHandler;
use Tests\Unit\Module\TacticianQueueInterop\Mock\LogMessageProducer;
use Tests\Unit\Module\TacticianQueueInterop\Mock\ProducerAwareQueueContext;
use Tests\Unit\TestCase;

class SendCommandToQueueHandlerTest extends TestCase
{
    public function testCommandMustBeSerializedAndSendToQueue(): void
    {
        $expectedCommand = new \stdClass();
        $expectedCommand->expectedCommandField = 'command field';

        $logMessageProducer = new LogMessageProducer();
        $psrContext = new ProducerAwareQueueContext($logMessageProducer);

        $sendCommandToQueueHandlerTest = new SendCommandToQueueHandler($psrContext);
        $sendCommandToQueueHandlerTest->handle(new QueueCommand($expectedCommand));

        $sentMessage = $logMessageProducer->getLastSentMessage();

        $this->assertNotEmpty($sentMessage);
        $this->assertEquals(serialize($expectedCommand), $sentMessage->getBody());
    }
}
