<?php

namespace Tests\Functional\Module\Mailgun\WebhookEvent;

use App\Module\Mailgun\WebhookEvent\Event\ComplainedAboutSpamEvent;
use App\Module\Mailgun\WebhookEvent\Event\DeliveryPermanentFailedEvent;
use App\Module\Mailgun\WebhookEvent\Event\UnsubscribedEvent;
use App\Module\Mailgun\WebhookEvent\Exception\InvalidWebhookRequestException;
use App\Module\Mailgun\WebhookEvent\Exception\UnexpectedEventTypeException;
use App\Module\Mailgun\WebhookEvent\Signature\SignatureGenerator;
use App\Module\Mailgun\WebhookEvent\WebhookEventFactory;
use App\Module\Mailgun\WebhookEvent\WebhookEventRequest;
use Tests\Functional\TestCase;

/**
 * @group mailgun
 */
class WebhookEventFactoryTest extends TestCase
{
    /** @var WebhookEventFactory */
    private $webhookEventFactory;
    /** @var SignatureGenerator */
    private $signatureGenerator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->webhookEventFactory = $this->getContainer()->get(WebhookEventFactory::class);
        $this->signatureGenerator = $this->getContainer()->get(SignatureGenerator::class);
    }

    protected function tearDown(): void
    {
        unset(
            $this->webhookEventFactory,
            $this->signatureGenerator
        );

        parent::tearDown();
    }

    public function testEventCanBeCreatedForValidRequest(): void
    {
        foreach ($this->getValidWebhookRequests() as $expectedEventType => $webhookEventRequest) {
            $event = $this->webhookEventFactory->createByRequest($webhookEventRequest, $expectedEventType);

            $this->assertInstanceOf($expectedEventType, $event);
            $this->assertEquals($webhookEventRequest->eventData['recipient'], $event->getRecipientEmail());
        }
    }

    public function testEventCatBeCreatedForInvalidRequest(): void
    {
        $this->expectException(InvalidWebhookRequestException::class);

        $invalidRequest = new WebhookEventRequest();

        $this->webhookEventFactory->createByRequest($invalidRequest);
    }

    public function testEventCantBeCreatedWithUnexpectedType(): void
    {
        $this->expectException(UnexpectedEventTypeException::class);

        $complainedAboutSpamEventRequest = new WebhookEventRequest();
        $complainedAboutSpamEventRequest->signature = $this->createValidSignature();
        $complainedAboutSpamEventRequest->eventData = [
            'event' => 'complained',
            'recipient' => 'foo@bar.com',
        ];

        $unexpectedType = DeliveryPermanentFailedEvent::class;

        $this->webhookEventFactory->createByRequest($complainedAboutSpamEventRequest, $unexpectedType);
    }

    private function getValidWebhookRequests(): \Generator
    {
        $complainedAboutSpamEventRequest = new WebhookEventRequest();
        $complainedAboutSpamEventRequest->signature = $this->createValidSignature();
        $complainedAboutSpamEventRequest->eventData = [
            'event' => 'complained',
            'recipient' => 'foo@bar.com',
        ];

        yield ComplainedAboutSpamEvent::class => $complainedAboutSpamEventRequest;

        $deliveryPermanentFailedEventRequest = new WebhookEventRequest();
        $deliveryPermanentFailedEventRequest->signature = $this->createValidSignature();
        $deliveryPermanentFailedEventRequest->eventData = [
            'event' => 'failed',
            'severity' => 'permanent',
            'recipient' => 'foo@bar.com',
        ];

        yield DeliveryPermanentFailedEvent::class => $deliveryPermanentFailedEventRequest;

        $unsubscribedEventRequest = new WebhookEventRequest();
        $unsubscribedEventRequest->signature = $this->createValidSignature();
        $unsubscribedEventRequest->eventData = [
            'event' => 'unsubscribed',
            'recipient' => 'foo@bar.com',
        ];

        yield UnsubscribedEvent::class => $unsubscribedEventRequest;
    }

    /**
     * @return string[]
     */
    private function createValidSignature(): array
    {
        $timestamp = time();
        $token = 'some-token';

        return [
            'timestamp' => $timestamp,
            'token' => $token,
            'signature' => $this->signatureGenerator->generate($token, $timestamp),
        ];
    }
}
