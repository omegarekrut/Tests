<?php

namespace Tests\Functional\Bridge\Forum\Webhook;

use App\Bridge\Xenforo\Webhook\Event\UserDeletedAlertEvent;
use App\Bridge\Xenforo\Webhook\Event\UserReadAlertsEvent;
use App\Bridge\Xenforo\Webhook\JsonWebhookEventsDeserializer;
use App\Bridge\Xenforo\Webhook\WebhookEventDenormalizer;
use Tests\Functional\TestCase;
use Tests\Unit\LoggerMock;

/**
 * @forum
 */
class WebhookEventsDeserializerTest extends TestCase
{
    /** @var JsonWebhookEventsDeserializer */
    private $webhookEventsDeserializer;
    /** @var LoggerMock */
    private $loggerMock;

    protected function setUp(): void
    {
        parent::setUp();

        $webhookEventDenormalizer = $this->getContainer()->get(WebhookEventDenormalizer::class);
        $this->loggerMock = new LoggerMock();

        $this->webhookEventsDeserializer = new JsonWebhookEventsDeserializer($webhookEventDenormalizer, $this->loggerMock);
    }

    protected function tearDown(): void
    {
        unset($this->loggerMock, $this->webhookEventsDeserializer);

        parent::tearDown();
    }

    public function testValidEventsCantBeBeDeserialized(): void
    {
        $eventsData = [
            [
                'event' => 'user.alerts_read',
                'data' => [
                    'alertedUserId' => 2,
                ],
            ],
            [
                'event' => 'user.alert_deleted',
                'data' => [
                    'alertId' => 1,
                    'alertedUserId' => 2,
                ],
            ]
        ];

        $events = iterator_to_array($this->webhookEventsDeserializer->deserialize(json_encode($eventsData)));

        $this->assertCount(2, $events);
        $this->assertInstanceOf(UserReadAlertsEvent::class, $events[0]);
        $this->assertEquals($eventsData[0]['data']['alertedUserId'], $events[0]->alertedUserId);
        $this->assertInstanceOf(UserDeletedAlertEvent::class, $events[1]);
        $this->assertEquals($eventsData[1]['data']['alertId'], $events[1]->alertId);
        $this->assertEquals($eventsData[1]['data']['alertedUserId'], $events[1]->alertedUserId);
    }

    public function testInvalidEventDatumErrorsMustBeSkippedAndLogged(): void
    {
        $actualException = null;
        $invalidEventsData = [[]];

        try {
            iterator_to_array($this->webhookEventsDeserializer->deserialize(json_encode($invalidEventsData)));
        } catch (\Throwable $exception) {
            $actualException = $exception;
        }

        $this->assertEmpty($actualException);

        $loggedMessages = $this->loggerMock->getMessages(false);

        $this->assertCount(1, $loggedMessages);
        $this->assertStringContainsString('Forum sent invalid event', $loggedMessages[0]['message']);
    }

    public function testOnlyValidEventsShouldBeDeserialized(): void
    {
        $eventsData = [
            [
                'event' => 'invalid event',
            ],
            [
                'event' => 'user.alerts_read',
                'data' => [
                    'alertedUserId' => 2,
                ],
            ],
        ];

        $events = iterator_to_array($this->webhookEventsDeserializer->deserialize(json_encode($eventsData)));

        $this->assertCount(1, $events);
        $this->assertInstanceOf(UserReadAlertsEvent::class, $events[0]);
    }
}
