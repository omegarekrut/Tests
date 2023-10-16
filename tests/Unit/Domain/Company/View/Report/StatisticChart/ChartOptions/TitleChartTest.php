<?php

namespace Tests\Unit\Domain\Company\View\Report\StatisticChart\ChartOptions;

use App\Domain\Company\View\Report\StatisticChart\ChartOptions\TitleChart;
use Tests\Unit\TestCase;

class TitleChartTest extends TestCase
{
    public function testGetChartData(): void
    {
        $expectedTitle = 'Some title';

        $titleChart = new TitleChart($expectedTitle);

        $expectedData = [
            'display' => true,
            'align' => 'start',
            'color' => '#212121',
            'font' => [
                'size' => 22,
                'lineHeight' => '24px',
            ],
            'text' => $expectedTitle,
        ];

        $this->assertEquals($expectedData, $titleChart->getChartData());
    }
}
