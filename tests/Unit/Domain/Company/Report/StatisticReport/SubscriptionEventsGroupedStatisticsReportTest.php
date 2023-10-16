<?php

namespace Tests\Unit\Domain\Company\Report\StatisticReport;

use App\Domain\Company\Collection\StatisticsCollection;
use App\Domain\Company\Entity\Statistics\CompanyCardStatistics;
use App\Domain\Company\Entity\Statistics\ValueObject\StatisticsType;
use App\Domain\Company\Report\StatisticReport\SubscriptionEventsGroupedStatisticsReport;
use Generator;
use RuntimeException;
use Tests\Unit\TestCase;

class SubscriptionEventsGroupedStatisticsReportTest extends TestCase
{
    public function testGetGroupEvents(): void
    {
        $expectedEvents = [
            StatisticsType::numberOfSubscriptions(),
            StatisticsType::numberOfUnsubscribes(),
        ];

        $this->assertEquals($expectedEvents, SubscriptionEventsGroupedStatisticsReport::getGroupEvents());
    }

    public function getUnsupportedStatisticsTypes(): Generator
    {
        foreach (StatisticsType::values() as $statisticsType) {
            if (in_array($statisticsType, SubscriptionEventsGroupedStatisticsReport::getGroupEvents())) {
                continue;
            }

            yield (string) $statisticsType => [$statisticsType];
        }
    }

    /**
     * @dataProvider getUnsupportedStatisticsTypes
     */
    public function testInitWithUnsupportedGroupEvents(StatisticsType $statisticsType): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(sprintf('Report does not contain support events %s', $statisticsType));

        $eventCollection = new StatisticsCollection();
        $eventCollection->add($this->getCompanyCardStatistics($statisticsType));

        new SubscriptionEventsGroupedStatisticsReport($eventCollection);
    }

    public function testGetSubscriptionsStatisticEvents(): void
    {
        $eventCollection = new StatisticsCollection();
        $eventCollection->add($this->getCompanyCardStatistics(StatisticsType::numberOfSubscriptions()));
        $eventCollection->add($this->getCompanyCardStatistics(StatisticsType::numberOfUnsubscribes()));
        $eventCollection->add($this->getCompanyCardStatistics(StatisticsType::numberOfSubscriptions()));

        $report = new SubscriptionEventsGroupedStatisticsReport($eventCollection);

        $subscriptionsStatisticEvents = $report->getSubscriptionsStatisticEvents();

        $this->assertCount(2, $subscriptionsStatisticEvents);
    }

    public function testGetUnsubscribesStatisticEvents(): void
    {
        $eventCollection = new StatisticsCollection();
        $eventCollection->add($this->getCompanyCardStatistics(StatisticsType::numberOfSubscriptions()));
        $eventCollection->add($this->getCompanyCardStatistics(StatisticsType::numberOfUnsubscribes()));
        $eventCollection->add($this->getCompanyCardStatistics(StatisticsType::numberOfSubscriptions()));

        $report = new SubscriptionEventsGroupedStatisticsReport($eventCollection);

        $unsubscribesStatisticEvents = $report->getUnsubscribesStatisticEvents();

        $this->assertCount(1, $unsubscribesStatisticEvents);
    }

    private function getCompanyCardStatistics(StatisticsType $statisticType): CompanyCardStatistics
    {
        $companyCardStatistics = $this->createMock(CompanyCardStatistics::class);

        $companyCardStatistics
            ->method('getType')
            ->willReturn($statisticType);

        return $companyCardStatistics;
    }
}
