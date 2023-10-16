<?php

namespace Tests\Unit\Bridge\ClientApp;

use App\Bridge\ClientApp\Provider\BannerProvider;
use Resolventa\Banner\Widget;
use Tests\Unit\TestCase;

class BannerProviderTest extends TestCase
{
    public function testRenderForPlace()
    {
        $bannerProvider = new BannerProvider($this->widgetMock());

        $bannerProvider->renderForPlace(1);
    }

    private function widgetMock(): Widget
    {
        $widget = $this->createMock(Widget::class);

        $widget
            ->expects($this->exactly(1))
            ->method('renderForPlace')
            ->willReturn('')
        ;

        return $widget;
    }
}
