<?php

namespace Tests\Unit\Domain\Company\View\Report\StatisticChart;

use App\Domain\Company\View\Report\StatisticChart\SubscribersChartView;
use Tests\Unit\TestCase;

class SubscribersChartViewTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->expectedSubsciprtionData = ['01.12' => 1, '05.12' => 5];
        $this->expectedUnsubsciprtionData = ['04.12' => 1, '20.12' => 1];

        $this->chartView = new SubscribersChartView(
            $this->expectedSubsciprtionData,
            $this->expectedUnsubsciprtionData,
        );
    }

    public function testGetChartData(): void
    {
        $expectedData = [
            'datasets' => [
                [
                    'label' => 'Подписалось',
                    'stack' => 'subscriptions',
                    'data' => $this->expectedSubsciprtionData,
                    'backgroundColor' => [
                        'rgba(89, 201, 73)',
                    ],
                ],
                [
                    'label' => 'Отписалось',
                    'stack' => 'subscriptions',
                    'data' => array_map(static fn(int $value): int => $value * -1, $this->expectedUnsubsciprtionData),
                    'backgroundColor' => [
                        'rgba(241, 128, 37)',
                    ],
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
                    'ticks' => [
                        'crossAlign' => 'center',
                        'color' => '#999999',
                        'maxTicksLimit' => 5,
                        'stepSize' => 1,
                    ],
                ],
            ],
            'plugins' => [
                'legend' => [
                    'position' => 'bottom',
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
                'text' => 'Подписчики',
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
                'text' => 'Количество подписавшихся и отписавшихся пользователей.',
            ],
        ];

        $this->assertEquals($expectedData, $this->chartView->getChartOptions());
    }
}
