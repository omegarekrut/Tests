<?php

namespace Tests\Unit\Module\Mailgun\WebhookEvent\Denormalizer;

use App\Module\Mailgun\WebhookEvent\Denormalizer\UnsubscribedEventDenormalizer;
use App\Module\Mailgun\WebhookEvent\Event\UnsubscribedEvent;
use App\Module\Mailgun\WebhookEvent\Exception\UnsupportedEventDataException;
use Tests\Unit\TestCase;

/**
 * @group mailgun
 */
class UnsubscribedEventDenormalizerTest extends TestCase
{
    /** @var UnsubscribedEventDenormalizer */
    private $denormalizer;

    protected function setUp(): void
    {
        $this->denormalizer = new UnsubscribedEventDenormalizer();
    }

    public function testDenormalizedEventShouldContainsExepectedRecipient(): void
    {
        $eventData = [
            'event' => 'unsubscribed',
            'recipient' => 'foo@bar.com',
        ];

        $unsubscribedEvent = $this->denormalizer->denormalize($eventData);

        $this->assertInstanceOf(UnsubscribedEvent::class, $unsubscribedEvent);
        $this->assertEquals($eventData['recipient'], $unsubscribedEvent->getRecipientEmail());
    }

    public function testDenormalizerCantDenormalizeUnsupportedEvent(): void
    {
        $this->expectException(UnsupportedEventDataException::class);
        $this->expectExceptionMessage('Event type must be equals unsubscribed');

        $invalidEventData = [
            'event' => 'complained',
        ];

        $this->denormalizer->denormalize($invalidEventData);
    }

    public function testDenormalizerCantDenormalizeEventWithoutRecipient(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Email recipient must be defined');

        $invalidEventData = [
            'event' => 'unsubscribed',
        ];

        $this->denormalizer->denormalize($invalidEventData);
    }
}
