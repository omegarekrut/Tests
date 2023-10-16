<?php

namespace Tests\Unit\Domain\Company\View\Report\StatisticChart;

use App\Domain\Company\View\Report\StatisticChart\AttendanceChartView;
use Tests\Unit\TestCase;

class AttendanceChartViewTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->attendanceData = ['01.12' => 1, '05.12' => 5];

        $this->chartView = new AttendanceChartView(
            $this->attendanceData,
        );
    }

    public function testGetChartData(): void
    {
        $expectedData = [
            'datasets' => [
                [
                    'label' => 'Посетителей',
                    'data' => $this->attendanceData,
                    'backgroundColor' => [
                        'rgba(204, 231, 242, 0.33)',
                    ],
                    'borderColor' => '#0085c0',
                    'fill' => true,
                    'tension' => 0.4,
                ],
            ],
        ];

        $this->assertEquals($expectedData, $this->chartView->getChartData());
    }

    public function testGetChartOptions(): void
    {
        $expectedData = [
            'spanGaps' => true,
            'maxBarThickness' => 15,
            'scales' => [
                'x' => [
                    'stacked' => true,
                    'grid' => [
                        'display' => false,
                        'drawOnChartArea' => false,
                    ],
                    'ticks' => [
                        'color' => '#999999',
                        'maxTicksLimit' => 5,
                    ],
                ],
                'y' => [
                    'min' => 0,
                    'max' => 6,
                    'ticks' => [
                        'color' => '#999999',
                        'maxTicksLimit' => 5,
                        'stepSize' => 10,
                    ],
                ],
            ],
            'plugins' => [
                'legend' => [
                    'display' => false,
                ],
            ],
            'title' => [
                'display' => true,
                'align' => 'start',
                'color' => '#212121',
                'font' => [
                    'size' => 22,
                    'lineHeight' => '24px',
                ],
                'text' => 'Посещаемость',
            ],
            'subtitle' => [
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
                'text' => 'Количество просмотров страницы профиля вашей компании.',
            ],
        ];

        $this->assertEquals($expectedData, $this->chartView->getChartOptions());
    }
}
