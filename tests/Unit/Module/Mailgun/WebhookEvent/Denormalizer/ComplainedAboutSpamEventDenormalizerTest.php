<?php

namespace Tests\Unit\Module\Mailgun\WebhookEvent\Denormalizer;

use App\Module\Mailgun\WebhookEvent\Event\ComplainedAboutSpamEvent;
use App\Module\Mailgun\WebhookEvent\Denormalizer\ComplainedAboutSpamEventDenormalizer;
use App\Module\Mailgun\WebhookEvent\Exception\UnsupportedEventDataException;
use Tests\Unit\TestCase;

/**
 * @group mailgun
 */
class ComplainedAboutSpamEventDenormalizerTest extends TestCase
{
    /** @var ComplainedAboutSpamEventDenormalizer */
    private $denormalizer;

    protected function setUp(): void
    {
        $this->denormalizer = new ComplainedAboutSpamEventDenormalizer();
    }

    public function testDenormalizedEventShouldContainsExepectedRecipient(): void
    {
        $eventData = [
            'event' => 'complained',
            'recipient' => 'foo@bar.com',
        ];

        $complainedAboutSpamEvent = $this->denormalizer->denormalize($eventData);

        $this->assertInstanceOf(ComplainedAboutSpamEvent::class, $complainedAboutSpamEvent);
        $this->assertEquals($eventData['recipient'], $complainedAboutSpamEvent->getRecipientEmail());
    }

    public function testDenormalizerCantDenormalizeUnsupportedEvent(): void
    {
        $this->expectException(UnsupportedEventDataException::class);
        $this->expectExceptionMessage('Event type must be equals complained');

        $invalidEventData = [
            'event' => 'failure',
        ];

        $this->denormalizer->denormalize($invalidEventData);
    }

    public function testDenormalizerCantDenormalizeEventWithoutRecipient(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Email recipient must be defined');

        $invalidEventData = [
            'event' => 'complained',
        ];

        $this->denormalizer->denormalize($invalidEventData);
    }
}
