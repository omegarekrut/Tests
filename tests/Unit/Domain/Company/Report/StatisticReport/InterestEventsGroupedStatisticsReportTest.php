<?php

namespace Tests\Unit\Domain\Company\Report\StatisticReport;

use App\Domain\Company\Collection\StatisticsCollection;
use App\Domain\Company\Entity\Statistics\CompanyCardStatistics;
use App\Domain\Company\Entity\Statistics\ValueObject\StatisticsType;
use App\Domain\Company\Report\StatisticReport\InterestEventsGroupedStatisticsReport;
use Generator;
use RuntimeException;
use Tests\Unit\TestCase;

class InterestEventsGroupedStatisticsReportTest extends TestCase
{
    public function testGetGroupEvents(): void
    {
        $expectedEvents = [
            StatisticsType::transitionsToCompanySite(),
            StatisticsType::transitionsToCompanySocialMedia(),
            StatisticsType::mapViews(),
            StatisticsType::phoneViews(),
            StatisticsType::whatsappViews(),
        ];

        $this->assertEquals($expectedEvents, InterestEventsGroupedStatisticsReport::getGroupEvents());
    }

    public function getUnsupportedStatisticsTypes(): Generator
    {
        foreach (StatisticsType::values() as $statisticsType) {
            if (in_array($statisticsType, InterestEventsGroupedStatisticsReport::getGroupEvents())) {
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

        new InterestEventsGroupedStatisticsReport($eventCollection);
    }

    public function testGetMapViewStatisticEvents(): void
    {
        $report = $this->getInterestEventsGroupedStatisticsReport();

        $subscriptionsStatisticEvents = $report->getMapViewStatisticEvents();

        $this->assertCount(2, $subscriptionsStatisticEvents);
    }

    public function testGetTransitionsToSocialMediaStatisticEvents(): void
    {
        $report = $this->getInterestEventsGroupedStatisticsReport();

        $subscriptionsStatisticEvents = $report->getTransitionsToSocialMediaStatisticEvents();

        $this->assertCount(2, $subscriptionsStatisticEvents);
    }

    public function testGetTransitionsToSiteStatisticEvents(): void
    {
        $report = $this->getInterestEventsGroupedStatisticsReport();

        $subscriptionsStatisticEvents = $report->getTransitionsToSiteStatisticEvents();

        $this->assertCount(2, $subscriptionsStatisticEvents);
    }

    public function testGetViewPhoneAndWhatsappStatisticEvents(): void
    {
        $report = $this->getInterestEventsGroupedStatisticsReport();

        $subscriptionsStatisticEvents = $report->getViewPhoneAndWhatsappStatisticEvents();

        $this->assertCount(4, $subscriptionsStatisticEvents);
    }

    private function getInterestEventsGroupedStatisticsReport(): InterestEventsGroupedStatisticsReport
    {
        $eventCollection = new StatisticsCollection();
        $eventCollection->add($this->getCompanyCardStatistics(StatisticsType::transitionsToCompanySite()));
        $eventCollection->add($this->getCompanyCardStatistics(StatisticsType::transitionsToCompanySocialMedia()));
        $eventCollection->add($this->getCompanyCardStatistics(StatisticsType::mapViews()));
        $eventCollection->add($this->getCompanyCardStatistics(StatisticsType::phoneViews()));
        $eventCollection->add($this->getCompanyCardStatistics(StatisticsType::whatsappViews()));
        $eventCollection->add($this->getCompanyCardStatistics(StatisticsType::transitionsToCompanySite()));
        $eventCollection->add($this->getCompanyCardStatistics(StatisticsType::transitionsToCompanySocialMedia()));
        $eventCollection->add($this->getCompanyCardStatistics(StatisticsType::mapViews()));
        $eventCollection->add($this->getCompanyCardStatistics(StatisticsType::phoneViews()));
        $eventCollection->add($this->getCompanyCardStatistics(StatisticsType::phoneViews()));

        return new InterestEventsGroupedStatisticsReport($eventCollection);
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
