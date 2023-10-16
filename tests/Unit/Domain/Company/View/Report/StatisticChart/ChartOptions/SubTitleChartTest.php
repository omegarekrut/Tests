<?php

namespace Tests\Unit\Domain\Company\View\Report\StatisticChart\ChartOptions;

use App\Domain\Company\View\Report\StatisticChart\ChartOptions\SubTitleChart;
use Tests\Unit\TestCase;

class SubTitleChartTest extends TestCase
{
    public function testGetChartData(): void
    {
        $expectedTitle = 'Some title';

        $titleChart = new SubTitleChart($expectedTitle);

        $expectedData = [
            'display' => true,
            'align' => 'start',
            'color' => '#999999',
            'font' => [
                'size' => 15,
                'lineHeight' => '24px',
            ],
            'padding' => [
                'bottom' => 20,
            ],
            'text' => $expectedTitle,
        ];

        $this->assertEquals($expectedData, $titleChart->getChartData());
    }
}
