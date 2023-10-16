<?php

namespace Tests\Unit\Domain\Company\View\Report;

use App\Domain\Company\Collection\StatisticsCollection;
use App\Domain\Company\Entity\Company;
use App\Domain\Company\Normalizer\CompanyStatisticChartNormalizer;
use App\Domain\Company\Report\GroupedStatisticReportFactory;
use App\Domain\Company\Report\StatisticReport\CompanyAttendanceEventsGroupedStatisticsReport;
use App\Domain\Company\Report\StatisticReport\InterestEventsGroupedStatisticsReport;
use App\Domain\Company\Report\StatisticReport\ReportDatePeriod;
use App\Domain\Company\Report\StatisticReport\SubscriptionEventsGroupedStatisticsReport;
use App\Domain\Company\View\Report\GroupedChartDataFromCompanyStatisticFactory;
use Tests\Unit\TestCase;

class GroupedChartDataFromCompanyStatisticFactoryTest extends TestCase
{
    public function testCreateChartsDataFromGroupedCompanyStatisticByPeriod(): void
    {
        $companyStatisticChartGroupsFactory = new GroupedChartDataFromCompanyStatisticFactory(
            $this->getGroupedStatisticReportFactory(),
            $this->getCompanyStatisticChartNormalizer(),
        );

        $data = $companyStatisticChartGroupsFactory->createForCompanyByPeriod(
            $this->createMock(Company::class),
            $this->createMock(ReportDatePeriod::class),
        );

        $this->assertArrayHasKey('attendance', $data);
        $this->assertArrayHasKey('interest', $data);
        $this->assertArrayHasKey('subscriptions', $data);

        $this->assertArrayHasKey('data', $data['attendance']);
        $this->assertArrayHasKey('options', $data['attendance']);

        $this->assertArrayHasKey('data', $data['interest']);
        $this->assertArrayHasKey('options', $data['interest']);

        $this->assertArrayHasKey('data', $data['subscriptions']);
        $this->assertArrayHasKey('options', $data['subscriptions']);
    }

    private function getCompanyStatisticChartNormalizer(): CompanyStatisticChartNormalizer
    {
        $companyStatisticChartNormalizer = $this->createMock(CompanyStatisticChartNormalizer::class);
        $companyStatisticChartNormalizer
            ->method('normalizeCompanyCollectionForChart')
            ->willReturn([]);

        return $companyStatisticChartNormalizer;
    }

    private function getGroupedStatisticReportFactory(): GroupedStatisticReportFactory
    {
        $statisticCollection = new StatisticsCollection();

        $groupedStatisticReportFactory = $this->createMock(GroupedStatisticReportFactory::class);
        $groupedStatisticReportFactory
            ->method('createSubscriptionsEventsGroupedStatisticsReport')
            ->willReturn(new SubscriptionEventsGroupedStatisticsReport($statisticCollection));

        $groupedStatisticReportFactory
            ->method('createInterestEventsGroupedStatisticsReport')
            ->willReturn(new InterestEventsGroupedStatisticsReport($statisticCollection));

        $groupedStatisticReportFactory
            ->method('createCompanyViewingEventsGroupedStatisticsReport')
            ->willReturn(new CompanyAttendanceEventsGroupedStatisticsReport($statisticCollection));

        return $groupedStatisticReportFactory;
    }
}
