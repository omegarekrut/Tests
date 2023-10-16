<?php

namespace Tests\Functional\Bridge\Forum\Webhook;

use App\Bridge\Xenforo\Webhook\Event\UserDeletedAlertEvent;
use App\Bridge\Xenforo\Webhook\Event\UserReadAlertsEvent;
use App\Bridge\Xenforo\Webhook\Event\UserReceivedAlertEvent;
use App\Bridge\Xenforo\Webhook\Exception\EventDoesNotSupportNormalizationException;
use App\Bridge\Xenforo\Webhook\Exception\InvalidEventDataException;
use App\Bridge\Xenforo\Webhook\WebhookEventDenormalizer;
use Tests\Functional\TestCase;

/**
 * @group forum
 */
class WebhookEventDenormalizerTest extends TestCase
{
    /** @var WebhookEventDenormalizer */
    private $webhookEventDenormalizer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->webhookEventDenormalizer = $this->getContainer()->get(WebhookEventDenormalizer::class);
    }

    protected function tearDown(): void
    {
        unset($this->webhookEventDenormalizer);

        parent::tearDown();
    }

    public function testUserReceivedAlertEventCanBeDenormalized(): void
    {
        $userReceivedAlertEventData = [
            'event' => 'user.alert_received',
            'data' => [
                'alertId' => 1,
                'alertedUserId' => 2,
                'userId' => 3,
                'action' => 'some action',
                'htmlMessage' => 'some html message',
            ],
        ];

        $event = $this->webhookEventDenormalizer->denormalize($userReceivedAlertEventData);

        $this->assertInstanceOf(UserReceivedAlertEvent::class, $event);
        /** @var UserReceivedAlertEvent $event */
        $this->assertEquals($userReceivedAlertEventData['data']['alertId'], $event->alertId);
        $this->assertEquals($userReceivedAlertEventData['data']['alertedUserId'], $event->alertedUserId);
        $this->assertEquals($userReceivedAlertEventData['data']['userId'], $event->userId);
        $this->assertEquals($userReceivedAlertEventData['data']['action'], $event->action);
        $this->assertEquals($userReceivedAlertEventData['data']['htmlMessage'], $event->htmlMessage);
    }

    public function testUserReadAlertsEventCanBeDenormalized(): void
    {
        $userReadAlertsEventData = [
            'event' => 'user.alerts_read',
            'data' => [
                'alertedUserId' => 2,
            ],
        ];

        $event = $this->webhookEventDenormalizer->denormalize($userReadAlertsEventData);

        $this->assertInstanceOf(UserReadAlertsEvent::class, $event);
        /** @var UserReadAlertsEvent $event */
        $this->assertEquals($userReadAlertsEventData['data']['alertedUserId'], $event->alertedUserId);
    }

    public function testUserDeletedAlertEventCanBeDenormalized(): void
    {
        $userDeletedAlertEventData = [
            'event' => 'user.alert_deleted',
            'data' => [
                'alertId' => 1,
                'alertedUserId' => 2,
            ],
        ];

        $event = $this->webhookEventDenormalizer->denormalize($userDeletedAlertEventData);

        $this->assertInstanceOf(UserDeletedAlertEvent::class, $event);
        /** @var UserDeletedAlertEvent $event */
        $this->assertEquals($userDeletedAlertEventData['data']['alertId'], $event->alertId);
        $this->assertEquals($userDeletedAlertEventData['data']['alertedUserId'], $event->alertedUserId);
    }

    /**
     * @dataProvider getUnsupportedEventsData
     */
    public function testAnyUnsupportedEventsByDataCantBeDenormalized(array $unsupportedEventData): void
    {
        $this->expectException(EventDoesNotSupportNormalizationException::class);

        $this->webhookEventDenormalizer->denormalize($unsupportedEventData);
    }

    public static function getUnsupportedEventsData(): \Generator
    {
        yield [
            [
                'event' => 'some unsupported event name',
            ]
        ];

        yield [
            [
                'event' => '',
            ]
        ];

        yield [
            []
        ];
    }

    public function testBrokenDataCantBeDenormalizedToEvent(): void
    {
        $this->expectException(InvalidEventDataException::class);

        $brokenEventData = [
            'event' => 'user.alerts_read',
            'data' => '',
        ];

        $this->webhookEventDenormalizer->denormalize($brokenEventData);
    }
}
