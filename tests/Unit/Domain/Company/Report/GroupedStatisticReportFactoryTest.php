<?php

namespace Tests\Unit\Domain\Company\Report;

use App\Domain\Company\Collection\StatisticsCollection;
use App\Domain\Company\Entity\Company;
use App\Domain\Company\Entity\Statistics\CompanyCardStatistics;
use App\Domain\Company\Entity\Statistics\ValueObject\StatisticsType;
use App\Domain\Company\Report\GroupedStatisticReportFactory;
use App\Domain\Company\Report\StatisticReport\CompanyAttendanceEventsGroupedStatisticsReport;
use App\Domain\Company\Report\StatisticReport\InterestEventsGroupedStatisticsReport;
use App\Domain\Company\Report\StatisticReport\SubscriptionEventsGroupedStatisticsReport;
use App\Domain\Company\Repository\CompanyCardStatisticsRepository;
use DateInterval;
use DatePeriod;
use DateTime;
use InvalidArgumentException;
use Tests\Unit\TestCase;

class GroupedStatisticReportFactoryTest extends TestCase
{
    private const EXPECTED_EVENTS_COUNT = 42;
    private const UNSUPPORTED_TYPE = 'SOME TYPE';

    private Company $expectedCompany;
    private DatePeriod $expectedPeriod;

    protected function setUp(): void
    {
        parent::setUp();

        $this->expectedCompany = $this->createMock(Company::class);
        $this->expectedPeriod = new DatePeriod(new DateTime(), new DateInterval('P1D'), new DateTime());
    }

    public function testCreateCompanyViewingEventsGroupedStatisticsReport(): void
    {
        $expectedEvents = CompanyAttendanceEventsGroupedStatisticsReport::getGroupEvents();
        $expectedStatistics = $this->getStatisticCollection(StatisticsType::viewCompanyPage());

        $companyCardStatisticsRepository = $this->getCompanyCardStatisticsRepository($this->expectedPeriod, $expectedEvents, $expectedStatistics);
        $reportFactory = new GroupedStatisticReportFactory($companyCardStatisticsRepository);

        $report = $reportFactory->createCompanyViewingEventsGroupedStatisticsReport($this->expectedCompany, $this->expectedPeriod);

        $this->assertEquals($expectedEvents, $report::getGroupEvents());
        $this->assertEquals($expectedStatistics, $report->getViewStatisticEvents());
    }

    public function testCreateInterestEventsGroupedStatisticsReport(): void
    {
        $expectedEvents = InterestEventsGroupedStatisticsReport::getGroupEvents();
        $expectedStatistics = $this->getStatisticCollection(StatisticsType::whatsappViews());

        $companyCardStatisticsRepository = $this->getCompanyCardStatisticsRepository($this->expectedPeriod, $expectedEvents, $expectedStatistics);
        $reportFactory = new GroupedStatisticReportFactory($companyCardStatisticsRepository);

        $report = $reportFactory->createInterestEventsGroupedStatisticsReport($this->expectedCompany, $this->expectedPeriod);

        $this->assertEquals($expectedEvents, $report::getGroupEvents());
        $this->assertEquals(0, $report->getTransitionsToSiteStatisticEvents()->getCountAll());
        $this->assertEquals(0, $report->getMapViewStatisticEvents()->getCountAll());
        $this->assertEquals(0, $report->getTransitionsToSocialMediaStatisticEvents()->getCountAll());
        $this->assertEquals($expectedStatistics, $report->getViewPhoneAndWhatsappStatisticEvents());
    }

    public function testCreateSubscriptionsEventsGroupedStatisticsReport(): void
    {
        $expectedEvents = SubscriptionEventsGroupedStatisticsReport::getGroupEvents();
        $expectedStatistics = $this->getStatisticCollection(StatisticsType::numberOfSubscriptions());

        $companyCardStatisticsRepository = $this->getCompanyCardStatisticsRepository($this->expectedPeriod, $expectedEvents, $expectedStatistics);
        $reportFactory = new GroupedStatisticReportFactory($companyCardStatisticsRepository);

        $report = $reportFactory->createSubscriptionsEventsGroupedStatisticsReport($this->expectedCompany, $this->expectedPeriod);

        $this->assertEquals($expectedEvents, $report::getGroupEvents());
        $this->assertEquals($expectedStatistics, $report->getSubscriptionsStatisticEvents());
        $this->assertEquals(0, $report->getUnsubscribesStatisticEvents()->getCountAll());
    }

    public function testGetCountStatisticEventsForPeriod(): void
    {
        $expectedTypes = [
            StatisticsType::numberOfSubscriptions(),
        ];

        $expectedStatistics = $this->getStatisticCollection(StatisticsType::numberOfSubscriptions());

        $companyCardStatisticsRepository = $this->getCompanyCardStatisticsRepository($this->expectedPeriod, $expectedTypes, $expectedStatistics);
        $reportFactory = new GroupedStatisticReportFactory($companyCardStatisticsRepository);

        $count = $reportFactory->getCountStatisticEventsForPeriod($this->expectedCompany, $expectedTypes, $this->expectedPeriod);

        $this->assertEquals(self::EXPECTED_EVENTS_COUNT, $count);
    }

    public function testAssertInvalidStatisticType(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Statistics type must be an instance of ');

        $companyCardStatisticsRepository = $this->getCompanyCardStatisticsRepository($this->expectedPeriod, [self::UNSUPPORTED_TYPE], new StatisticsCollection());
        $reportFactory = new GroupedStatisticReportFactory($companyCardStatisticsRepository);

        $reportFactory->getCountStatisticEventsForPeriod($this->expectedCompany, [self::UNSUPPORTED_TYPE], $this->expectedPeriod);
    }

    private function getCompanyCardStatisticsRepository(DatePeriod $expectedPeriod, array $expectedStatisticTypes, StatisticsCollection $expectedStatistics): CompanyCardStatisticsRepository
    {
        $companyCardStatisticsRepository = $this->createMock(CompanyCardStatisticsRepository::class);
        $companyCardStatisticsRepository
            ->method('findForCompanyByPeriodAndTypes')
            ->with($this->expectedCompany, $expectedPeriod, $expectedStatisticTypes)
            ->willReturn($expectedStatistics);

        return $companyCardStatisticsRepository;
    }

    private function getStatisticCollection(StatisticsType $statisticsType): StatisticsCollection
    {
        $companyCardStatistic = $this->createMock(CompanyCardStatistics::class);
        $companyCardStatistic
            ->method('getCount')
            ->willReturn(self::EXPECTED_EVENTS_COUNT);
        $companyCardStatistic
            ->method('getType')
            ->willReturn($statisticsType);

        $statisticCollection = new StatisticsCollection();
        $statisticCollection->add($companyCardStatistic);

        return $statisticCollection;
    }
}
