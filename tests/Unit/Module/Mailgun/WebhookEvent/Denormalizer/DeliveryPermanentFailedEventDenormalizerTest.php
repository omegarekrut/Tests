<?php

namespace Tests\Unit\Module\Mailgun\WebhookEvent\Denormalizer;

use App\Module\Mailgun\WebhookEvent\Event\DeliveryPermanentFailedEvent;
use App\Module\Mailgun\WebhookEvent\Denormalizer\DeliveryPermanentFailedEventDenormalizer;
use App\Module\Mailgun\WebhookEvent\Exception\UnsupportedEventDataException;
use Tests\Unit\TestCase;

/**
 * @group mailgun
 */
class DeliveryPermanentFailedEventDenormalizerTest extends TestCase
{
    /** @var DeliveryPermanentFailedEventDenormalizer */
    private $denormalizer;

    protected function setUp(): void
    {
        $this->denormalizer = new DeliveryPermanentFailedEventDenormalizer();
    }

    public function testDenormalizedEventShouldContainsExepectedRecipient(): void
    {
        $eventData = [
            'event' => 'failed',
            'severity' => 'permanent',
            'recipient' => 'foo@bar.com',
        ];

        $deliveryPermanentFailedEvent = $this->denormalizer->denormalize($eventData);

        $this->assertInstanceOf(DeliveryPermanentFailedEvent::class, $deliveryPermanentFailedEvent);
        $this->assertEquals($eventData['recipient'], $deliveryPermanentFailedEvent->getRecipientEmail());
    }

    public function testDenormalizerCantDenormalizeUnsupportedEvent(): void
    {
        $this->expectException(UnsupportedEventDataException::class);
        $this->expectExceptionMessage('Event type must be equals failed');

        $invalidEventData = [
            'event' => 'complained',
        ];

        $this->denormalizer->denormalize($invalidEventData);
    }

    public function testDenormalizerCantDenormalizeNotPermanentFailureEvent(): void
    {
        $this->expectException(UnsupportedEventDataException::class);
        $this->expectExceptionMessage('Event severity must be equals permanent');

        $invalidEventData = [
            'event' => 'failed',
            'severity' => 'not-permanent',
        ];

        $this->denormalizer->denormalize($invalidEventData);
    }

    public function testDenormalizerCantDenormalizeEventWithoutRecipient(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Email recipient must be defined');

        $invalidEventData = [
            'event' => 'failed',
            'severity' => 'permanent',
        ];

        $this->denormalizer->denormalize($invalidEventData);
    }
}
