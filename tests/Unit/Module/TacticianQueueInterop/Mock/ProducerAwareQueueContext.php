<?php

namespace Tests\Unit\Module\TacticianQueueInterop\Mock;

use Enqueue\Null\NullContext;
use Interop\Queue\PsrProducer;

class ProducerAwareQueueContext extends NullContext
{
    private $producer;

    public function __construct(PsrProducer $producer)
    {
        $this->producer = $producer;
    }

    public function createProducer(): PsrProducer
    {
        return $this->producer;
    }
}
