<?php

namespace Tests\Unit\Domain\Statistic\View;

use App\Domain\Statistic\Entity\StatisticReport\UsersActivityStatisticReport;
use App\Domain\Statistic\Normalizer\StatisticReportGoogleChartNormalizer;
use App\Domain\Statistic\View\StatisticReportViewFactory;
use Tests\Unit\TestCase;

/**
 * @group statistic
 */
class StatisticReportViewFactoryTest extends TestCase
{
    public function testReportViewCanBeCreatedForReport(): void
    {
        $expectedMetricLabel = 'metric label';
        $report = $this->createMock(UsersActivityStatisticReport::class);

        $reportViewFactory = new StatisticReportViewFactory($this->createMock(StatisticReportGoogleChartNormalizer::class));
        $reportView = $reportViewFactory->create($expectedMetricLabel, $report);

        $this->assertTrue($reportView->hasReport);
        $this->assertEquals($expectedMetricLabel, $reportView->metricLabel);
    }

    public function testViewUnitsMustBeFormattedForGoogleChart(): void
    {
        $expectedDataForGoogleChart = ['expected data for google chart'];
        $report = $this->createMock(UsersActivityStatisticReport::class);

        $reportUnitsNormalizer = $this->createConfiguredMock(StatisticReportGoogleChartNormalizer::class, [
            'normalize' => $expectedDataForGoogleChart,
        ]);

        $reportViewFactory = new StatisticReportViewFactory($reportUnitsNormalizer);
        $reportView = $reportViewFactory->create('label', $report);

        $this->assertEquals($expectedDataForGoogleChart, $reportView->reportUnits);
    }

    public function testViewMustNotHasReportForEmptyProvidedReport(): void
    {
        $reportViewFactory = new StatisticReportViewFactory($this->createMock(StatisticReportGoogleChartNormalizer::class));
        $reportView = $reportViewFactory->create('label', null);

        $this->assertFalse($reportView->hasReport);
    }
}
