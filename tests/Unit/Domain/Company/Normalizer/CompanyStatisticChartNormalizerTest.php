<?php

namespace Tests\Unit\Domain\Company\Normalizer;

use App\Domain\Company\Collection\StatisticsCollection;
use App\Domain\Company\Entity\Statistics\CompanyCardStatistics;
use App\Domain\Company\Normalizer\CompanyStatisticChartNormalizer;
use App\Domain\Company\Report\ReportDatePeriodGenerator;
use App\Domain\Company\Report\StatisticReport\ReportDatePeriod;
use DateTimeImmutable;
use DateTimeInterface;
use Tests\Unit\TestCase;

class CompanyStatisticChartNormalizerTest extends TestCase
{
    public function testNormalizeDataForEmptyCollectionAndDayInterval(): void
    {
        $companyStatisticChartNormalizer = new CompanyStatisticChartNormalizer();

        $data = $companyStatisticChartNormalizer->normalizeCompanyCollectionForChart(
            new StatisticsCollection(),
            $this->getReportDatePeriodWithDayInterval()
        );

        $expectedData = [
            '06.11' => 0,
            '07.11' => 0,
            '08.11' => 0,
            '09.11' => 0,
            '10.11' => 0,
            '11.11' => 0,
            '12.11' => 0,
            '13.11' => 0,
            '14.11' => 0,
            '15.11' => 0,
            '16.11' => 0,
            '17.11' => 0,
            '18.11' => 0,
            '19.11' => 0,
            '20.11' => 0,
            '21.11' => 0,
            '22.11' => 0,
        ];

        $this->assertEquals($expectedData, $data);
    }

    public function testNormalizeDataForEmptyCollectionAndMonthInterval(): void
    {
        $companyStatisticChartNormalizer = new CompanyStatisticChartNormalizer();

        $data = $companyStatisticChartNormalizer->normalizeCompanyCollectionForChart(
            new StatisticsCollection(),
            $this->getReportDatePeriodWithMonthInterval()
        );

        $expectedData = [
            '04.2022' => 0,
            '05.2022' => 0,
            '06.2022' => 0,
            '07.2022' => 0,
            '08.2022' => 0,
            '09.2022' => 0,
            '10.2022' => 0,
            '11.2022' => 0,
        ];

        $this->assertEquals($expectedData, $data);
    }

    public function testNormalizeDataWithDayInterval(): void
    {
        $companyStatisticChartNormalizer = new CompanyStatisticChartNormalizer();

        $data = $companyStatisticChartNormalizer->normalizeCompanyCollectionForChart(
            $this->getFilledStatisticsCollection(),
            $this->getReportDatePeriodWithDayInterval()
        );

        $expectedData = [
            '06.11' => 5,
            '07.11' => 0,
            '08.11' => 0,
            '09.11' => 0,
            '10.11' => 0,
            '11.11' => 0,
            '12.11' => 0,
            '13.11' => 0,
            '14.11' => 0,
            '15.11' => 0,
            '16.11' => 0,
            '17.11' => 0,
            '18.11' => 2,
            '19.11' => 0,
            '20.11' => 0,
            '21.11' => 1,
            '22.11' => 6,
        ];

        $this->assertEquals($expectedData, $data);
    }

    public function testNormalizeDataWithMonthInterval(): void
    {
        $companyStatisticChartNormalizer = new CompanyStatisticChartNormalizer();

        $data = $companyStatisticChartNormalizer->normalizeCompanyCollectionForChart(
            $this->getFilledStatisticsCollection(),
            $this->getReportDatePeriodWithMonthInterval()
        );

        $expectedData = [
            '04.2022' => 2,
            '05.2022' => 0,
            '06.2022' => 3,
            '07.2022' => 4,
            '08.2022' => 0,
            '09.2022' => 5,
            '10.2022' => 2,
            '11.2022' => 14,
        ];

        $this->assertEquals($expectedData, $data);
    }

    private function getFilledStatisticsCollection(): StatisticsCollection
    {
        $statisticsCollection = new StatisticsCollection();


        $statisticsCollection->add(
            $this->getCompanyCardStatistics(2, new DateTimeImmutable('2022-04-09'))
        );
        $statisticsCollection->add(
            $this->getCompanyCardStatistics(1, new DateTimeImmutable('2022-06-13'))
        );
        $statisticsCollection->add(
            $this->getCompanyCardStatistics(2, new DateTimeImmutable('2022-06-24'))
        );
        $statisticsCollection->add(
            $this->getCompanyCardStatistics(4, new DateTimeImmutable('2022-07-07'))
        );
        $statisticsCollection->add(
            $this->getCompanyCardStatistics(5, new DateTimeImmutable('2022-09-10'))
        );
        $statisticsCollection->add(
            $this->getCompanyCardStatistics(2, new DateTimeImmutable('2022-11-01'))
        );
        $statisticsCollection->add(
            $this->getCompanyCardStatistics(0, new DateTimeImmutable('2022-11-06'))
        );
        $statisticsCollection->add(
            $this->getCompanyCardStatistics(2, new DateTimeImmutable('2022-11-06'))
        );
        $statisticsCollection->add(
            $this->getCompanyCardStatistics(3, new DateTimeImmutable('2022-11-06'))
        );
        $statisticsCollection->add(
            $this->getCompanyCardStatistics(2, new DateTimeImmutable('2022-11-18'))
        );
        $statisticsCollection->add(
            $this->getCompanyCardStatistics(1, new DateTimeImmutable('2022-11-21'))
        );
        $statisticsCollection->add(
            $this->getCompanyCardStatistics(3, new DateTimeImmutable('2022-11-22'))
        );
        $statisticsCollection->add(
            $this->getCompanyCardStatistics(3, new DateTimeImmutable('2022-11-22'))
        );

        return $statisticsCollection;
    }

    private function getCompanyCardStatistics(int $count, DateTimeInterface $trackingDate): CompanyCardStatistics
    {
        $companyCardStatistics = $this->createMock(CompanyCardStatistics::class);

        $companyCardStatistics
            ->method('getCount')
            ->willReturn($count);

        $companyCardStatistics
            ->method('getTrackingDate')
            ->willReturn($trackingDate);

        return $companyCardStatistics;
    }

    private function getReportDatePeriodWithDayInterval(): ReportDatePeriod
    {
        $dayFrom = new DateTimeImmutable('2022-11-06');
        $dayTo = new DateTimeImmutable('2022-11-22');

        $reportDatePeriodGenerator = new ReportDatePeriodGenerator();

        return $reportDatePeriodGenerator->createFromDates($dayFrom, $dayTo);
    }

    private function getReportDatePeriodWithMonthInterval(): ReportDatePeriod
    {
        $dayFrom = new DateTimeImmutable('2022-04-06');
        $dayTo = new DateTimeImmutable('2022-11-22');

        $reportDatePeriodGenerator = new ReportDatePeriodGenerator();

        return $reportDatePeriodGenerator->createFromDates($dayFrom, $dayTo);
    }
}
