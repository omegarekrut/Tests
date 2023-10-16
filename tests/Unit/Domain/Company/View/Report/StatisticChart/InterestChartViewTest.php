<?php

namespace Tests\Unit\Domain\Company\View\Report\StatisticChart;

use App\Domain\Company\View\Report\StatisticChart\InterestChartView;
use Tests\Unit\TestCase;

class InterestChartViewTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->mapViewStatisticEvents = ['01.12' => 1, '05.12' => 5];
        $this->transitionsToSocialMediaStatisticEvents = ['04.12' => 1, '09.12' => 1];
        $this->transitionsToSiteStatisticEvents = ['02.12' => 1, '04.12' => 1];
        $this->viewPhoneAndWhatsappStatisticEvents = ['17.12' => 1, '20.12' => 1];

        $this->chartView = new InterestChartView(
            $this->mapViewStatisticEvents,
            $this->transitionsToSocialMediaStatisticEvents,
            $this->transitionsToSiteStatisticEvents,
            $this->viewPhoneAndWhatsappStatisticEvents,
        );
    }

    public function testGetChartData(): void
    {
        $expectedData = [
            'datasets' => [
                [
                    'label' => 'Просмотры компании на общей карте',
                    'stack' => 'interest',
                    'data' => $this->mapViewStatisticEvents,
                    'backgroundColor' => [
                        'rgba(123, 192, 247)',
                    ],
                ],
                [
                    'label' => 'Просмотры номеров телефонов',
                    'stack' => 'interest',
                    'data' => $this->viewPhoneAndWhatsappStatisticEvents,
                    'backgroundColor' => [
                        'rgba(0, 133, 192)',
                    ],
                ],
                [
                    'label' => 'Переход на сайт компании',
                    'stack' => 'interest',
                    'data' => $this->transitionsToSiteStatisticEvents,
                    'backgroundColor' => [
                        'rgba(241, 128, 37)',
                    ],
                ],
                [
                    'label' => 'Переходы на соц сети',
                    'stack' => 'interest',
                    'data' => $this->transitionsToSocialMediaStatisticEvents,
                    'backgroundColor' => [
                        'rgba(255, 219, 105)',
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
                    'min' => 0,
                    'ticks' => [
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
                'text' => 'Заинтересованность',
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
                'text' => 'Количество просмотров контактных данных и переходов по внешним ссылкам.',
            ],
        ];

        $this->assertEquals($expectedData, $this->chartView->getChartOptions());
    }
}
