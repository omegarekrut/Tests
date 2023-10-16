<?php

namespace Tests\Unit\Module\TacticianQueueInterop\Mock;

use Enqueue\Null\NullProducer;
use Interop\Queue\PsrDestination;
use Interop\Queue\PsrMessage;

class LogMessageProducer extends NullProducer
{
    private $sentMessages = [];

    public function send(PsrDestination $destination, PsrMessage $message): void
    {
        $this->sentMessages[] = $message;
    }

    public function getLastSentMessage(): ?PsrMessage
    {
        return count($this->sentMessages) ? $this->sentMessages[count($this->sentMessages) - 1] : null;
    }
}
