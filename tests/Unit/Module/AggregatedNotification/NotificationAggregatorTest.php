<?php

namespace Tests\Unit\Module\AggregatedNotification;

use App\Module\AggregatedNotification\AggregatableNotificationInterface;
use App\Module\AggregatedNotification\AggregatedNotificationFactoryInterface;
use App\Module\AggregatedNotification\AggregatedNotificationInterface;
use App\Module\AggregatedNotification\NotificationAggregator;
use Tests\Unit\TestCase;

/**
 * @group notification
 */
class NotificationAggregatorTest extends TestCase
{
    /** @var NotificationAggregator */
    private $aggregator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->aggregator = new NotificationAggregator($this->createAggregatedNotificationFactory());
    }

    public function testNotificationsShouldBeAggregatedByGroup(): void
    {
        $firstGroupNotifications = [
            $this->createAggregatableNotification('first-group'),
            $this->createAggregatableNotification('first-group'),
            $this->createAggregatableNotification('first-group'),
        ];

        $secondGroupNotifications = [
            $this->createAggregatableNotification('second-group'),
            $this->createAggregatableNotification('second-group'),
        ];

        $aggregatedNotifications = iterator_to_array($this->aggregator->aggregate(array_merge(
            $firstGroupNotifications,
            $secondGroupNotifications
        )));

        $this->assertCount(2, $aggregatedNotifications);
        $this->assertInstanceOf(AggregatedNotificationInterface::class, $aggregatedNotifications[0]);
        $this->assertEquals($firstGroupNotifications, $aggregatedNotifications[0]->getNotifications());
        $this->assertInstanceOf(AggregatedNotificationInterface::class, $aggregatedNotifications[1]);
        $this->assertEquals($secondGroupNotifications, $aggregatedNotifications[1]->getNotifications());
    }

    public function testNotificationsUnsupportedAggregationShouldStayImmutable(): void
    {
        $unsupportedAggregationNotification = $this;

        $aggregatedNotifications = iterator_to_array($this->aggregator->aggregate([
            $unsupportedAggregationNotification
        ]));

        $this->assertCount(1, $aggregatedNotifications);
        $this->assertTrue($this === $aggregatedNotifications[0]);
    }

    public function testSingleNotificationShouldStayImmutable(): void
    {
        $notification = $this->createAggregatableNotification('some-group');

        $aggregatedNotifications = iterator_to_array($this->aggregator->aggregate([
            $notification
        ]));

        $this->assertCount(1, $aggregatedNotifications);
        $this->assertTrue($notification === $aggregatedNotifications[0]);
    }

    private function createAggregatableNotification(string $aggregationGroup): AggregatableNotificationInterface
    {
        $stub = $this->createMock(AggregatableNotificationInterface::class);
        $stub
            ->method('getAggregationGroup')
            ->willReturn($aggregationGroup);

        return $stub;
    }

    private function createAggregatedNotificationFactory(): AggregatedNotificationFactoryInterface
    {
        $factory = $this->createMock(AggregatedNotificationFactoryInterface::class);
        $factory
            ->method('create')
            ->willReturnCallback(function (AggregatableNotificationInterface $sourceNotification, iterable $notifications) {
                $notification = $this->createMock(AggregatedNotificationInterface::class);
                $notification
                    ->method('getSourceNotification')
                    ->willReturn($sourceNotification);
                $notification
                    ->method('getNotifications')
                    ->willReturn($notifications);

                return $notification;
            });

        return $factory;
    }
}
