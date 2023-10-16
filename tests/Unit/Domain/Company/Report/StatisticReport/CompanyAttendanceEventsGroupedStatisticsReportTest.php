<?php

namespace Tests\Unit\Domain\Company\Report\StatisticReport;

use App\Domain\Company\Collection\StatisticsCollection;
use App\Domain\Company\Entity\Statistics\CompanyCardStatistics;
use App\Domain\Company\Entity\Statistics\ValueObject\StatisticsType;
use App\Domain\Company\Report\StatisticReport\CompanyAttendanceEventsGroupedStatisticsReport;
use Generator;
use RuntimeException;
use Tests\Unit\TestCase;

class CompanyAttendanceEventsGroupedStatisticsReportTest extends TestCase
{
    public function testGetGroupEvents(): void
    {
        $expectedEvents = [
            StatisticsType::viewCompanyPage(),
        ];

        $this->assertEquals($expectedEvents, CompanyAttendanceEventsGroupedStatisticsReport::getGroupEvents());
    }

    /**
     * @dataProvider getUnsupportedStatisticsTypes
     */
    public function testInitWithUnsupportedGroupEvents(StatisticsType $statisticsType): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(sprintf('Report does not contain supported events %s', $statisticsType));

        $eventCollection = new StatisticsCollection();
        $eventCollection->add($this->getCompanyCardStatistics($statisticsType));

        new CompanyAttendanceEventsGroupedStatisticsReport($eventCollection);
    }

    public function getUnsupportedStatisticsTypes(): Generator
    {
        foreach (StatisticsType::values() as $statisticsType) {
            if (in_array($statisticsType, CompanyAttendanceEventsGroupedStatisticsReport::getGroupEvents())) {
                continue;
            }

            yield (string) $statisticsType => [$statisticsType];
        }
    }

    public function testGetViewStatisticEvents(): void
    {
        $eventCollection = new StatisticsCollection();
        $eventCollection->add($this->getCompanyCardStatistics(StatisticsType::viewCompanyPage()));
        $eventCollection->add($this->getCompanyCardStatistics(StatisticsType::viewCompanyPage()));

        $report = new CompanyAttendanceEventsGroupedStatisticsReport($eventCollection);

        $viewStatisticEvents = $report->getViewStatisticEvents();

        $this->assertCount(2, $viewStatisticEvents);
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
