<?php

namespace Tests\Unit\Domain\Company\View\Report;

use App\Domain\Company\Entity\Company;
use App\Domain\Company\Report\GroupedStatisticReportFactory;
use App\Domain\Company\View\Report\CompanyStatisticChangeLastMonthTilesFactory;
use App\Domain\Company\View\Report\StatisticTile\CompanyViewingTile;
use App\Domain\Company\View\Report\StatisticTile\StatisticsChangeType;
use App\Domain\Company\View\Report\StatisticTile\TransitionToSocialNetworksTile;
use Tests\Unit\TestCase;

class CompanyStatisticChangeLastMonthTilesFactoryTest extends TestCase
{
    private const EXPECTED_CURRENT_MONTH_COUNT = 42;

    private Company $company;

    protected function setUp(): void
    {
        parent::setUp();

        $this->company = $this->createMock(Company::class);
    }

    public function testCreatePositiveCompanyViewingEventsGroupedStatisticsView(): void
    {
        $shortMonthStatisticFactory = $this->getShortMonthStatisticFactoryWithPositiveCompanyCardStatistics();
        $companyViewingTile = $shortMonthStatisticFactory->createCompanyViewingTile($this->company);

        $this->assertInstanceOf(CompanyViewingTile::class, $companyViewingTile);
        $this->assertEquals(self::EXPECTED_CURRENT_MONTH_COUNT, $companyViewingTile->getCount());
        $this->assertTrue($companyViewingTile->getMonthChangeType()->equals(StatisticsChangeType::positiveChangeType()));
    }

    public function testCreateNegativeCompanyViewingEventsGroupedStatisticsView(): void
    {
        $shortMonthStatisticFactory = $this->getShortMonthStatisticFactoryWithNegativeCompanyCardStatistics();
        $companyViewingTile = $shortMonthStatisticFactory->createCompanyViewingTile($this->company);

        $this->assertInstanceOf(CompanyViewingTile::class, $companyViewingTile);
        $this->assertEquals(self::EXPECTED_CURRENT_MONTH_COUNT, $companyViewingTile->getCount());
        $this->assertTrue($companyViewingTile->getMonthChangeType()->equals(StatisticsChangeType::negativeChangeType()));
    }

    public function testCreateUnchangedCompanyViewingEventsGroupedStatisticsView(): void
    {
        $shortMonthStatisticFactory = $this->getShortMonthStatisticFactoryWithUnchangedCompanyCardStatistics();
        $companyViewingTile = $shortMonthStatisticFactory->createCompanyViewingTile($this->company);

        $this->assertInstanceOf(CompanyViewingTile::class, $companyViewingTile);
        $this->assertEquals(self::EXPECTED_CURRENT_MONTH_COUNT, $companyViewingTile->getCount());
        $this->assertTrue($companyViewingTile->getMonthChangeType()->equals(StatisticsChangeType::unchangedType()));
    }

    public function testCreatePositiveTransitionToSocialNetworksEventsStatisticsView(): void
    {
        $shortMonthStatisticFactory = $this->getShortMonthStatisticFactoryWithPositiveCompanyCardStatistics();
        $transitionToSocialNetworksEventsStatisticsView = $shortMonthStatisticFactory->createTransitionToSocialNetworksTile($this->company);

        $this->assertInstanceOf(TransitionToSocialNetworksTile::class, $transitionToSocialNetworksEventsStatisticsView);
        $this->assertEquals(self::EXPECTED_CURRENT_MONTH_COUNT, $transitionToSocialNetworksEventsStatisticsView->getCount());
        $this->assertTrue($transitionToSocialNetworksEventsStatisticsView->getMonthChangeType()->equals(StatisticsChangeType::positiveChangeType()));
    }

    public function testCreateNegativeTransitionToSocialNetworksEventsStatisticsView(): void
    {
        $shortMonthStatisticFactory = $this->getShortMonthStatisticFactoryWithPositiveCompanyCardStatistics();
        $transitionToSocialNetworksEventsStatisticsView = $shortMonthStatisticFactory->createTransitionToSocialNetworksTile($this->company);

        $this->assertInstanceOf(TransitionToSocialNetworksTile::class, $transitionToSocialNetworksEventsStatisticsView);
        $this->assertEquals(self::EXPECTED_CURRENT_MONTH_COUNT, $transitionToSocialNetworksEventsStatisticsView->getCount());
        $this->assertTrue($transitionToSocialNetworksEventsStatisticsView->getMonthChangeType()->equals(StatisticsChangeType::positiveChangeType()));
    }

    public function testCreateUnchangedTransitionToSocialNetworksEventsStatisticsView(): void
    {
        $shortMonthStatisticFactory = $this->getShortMonthStatisticFactoryWithPositiveCompanyCardStatistics();
        $transitionToSocialNetworksEventsStatisticsView = $shortMonthStatisticFactory->createTransitionToSocialNetworksTile($this->company);

        $this->assertInstanceOf(TransitionToSocialNetworksTile::class, $transitionToSocialNetworksEventsStatisticsView);
        $this->assertEquals(self::EXPECTED_CURRENT_MONTH_COUNT, $transitionToSocialNetworksEventsStatisticsView->getCount());
        $this->assertTrue($transitionToSocialNetworksEventsStatisticsView->getMonthChangeType()->equals(StatisticsChangeType::positiveChangeType()));
    }

    private function getShortMonthStatisticFactoryWithPositiveCompanyCardStatistics(): CompanyStatisticChangeLastMonthTilesFactory
    {
        return $this->getShortMonthStatisticFactory(
            self::EXPECTED_CURRENT_MONTH_COUNT,
            self::EXPECTED_CURRENT_MONTH_COUNT - 1
        );
    }

    private function getShortMonthStatisticFactoryWithNegativeCompanyCardStatistics(): CompanyStatisticChangeLastMonthTilesFactory
    {
        return $this->getShortMonthStatisticFactory(
            self::EXPECTED_CURRENT_MONTH_COUNT,
            self::EXPECTED_CURRENT_MONTH_COUNT + 1
        );
    }

    private function getShortMonthStatisticFactoryWithUnchangedCompanyCardStatistics(): CompanyStatisticChangeLastMonthTilesFactory
    {
        return $this->getShortMonthStatisticFactory(
            self::EXPECTED_CURRENT_MONTH_COUNT,
            self::EXPECTED_CURRENT_MONTH_COUNT
        );
    }

    private function getShortMonthStatisticFactory(int $currentMonthCount, int $prevMonthCount): CompanyStatisticChangeLastMonthTilesFactory
    {
        return new CompanyStatisticChangeLastMonthTilesFactory($this->getGroupedStatisticReportFactory($currentMonthCount, $prevMonthCount));
    }

    private function getGroupedStatisticReportFactory(int $currentMonthCount, int $prevMonthCount): GroupedStatisticReportFactory
    {
        $companyCardStatisticsRepository = $this->createMock(GroupedStatisticReportFactory::class);
        $companyCardStatisticsRepository
            ->method('getCountStatisticEventsForPeriod')
            ->willReturnOnConsecutiveCalls(
                $currentMonthCount,
                $prevMonthCount,
            );

        return $companyCardStatisticsRepository;
    }
}
