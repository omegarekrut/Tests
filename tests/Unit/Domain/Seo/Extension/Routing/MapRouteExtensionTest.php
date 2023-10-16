<?php

namespace Tests\Unit\Domain\Seo\Extension\Routing;

use App\Domain\Record\Common\View\RecordViewMetadata;
use App\Domain\Record\Map\View\MapView;
use App\Domain\Seo\Extension\Routing\MapRouteExtension;
use App\Module\Seo\Extension\Routing\Route;
use App\Module\Seo\Extension\SeoContext;
use App\Module\Seo\TransferObject\SeoPage;
use Tests\Traits\RouteExtensionTrait;
use Tests\Unit\TestCase;
use Laminas\Diactoros\Uri;

class MapRouteExtensionTest extends TestCase
{
    use RouteExtensionTrait;

    /** @var SeoPage */
    private $seoPage;
    /** @var MapRouteExtension */
    private $pageRouteExtension;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seoPage = new SeoPage();
        $this->pageRouteExtension = new MapRouteExtension($this->createBreadcrumbsFactoryMock(), $this->createUrlGeneratorMock());
    }

    /**
     * @dataProvider getRoutesForCheckSupports
     */
    public function testIsSupportedRoutes(string $routeName, bool $expectedIsSupported): void
    {
        $route = $this->createConfiguredMock(Route::class, [
            'getName' => $routeName,
        ]);

        $this->assertEquals($expectedIsSupported, $this->pageRouteExtension->isSupported($route));
    }

    public function getRoutesForCheckSupports(): array
    {
        return [
            'map_marker_edit' => ['map_marker_edit', true],
            'map_marker_view' => ['map_marker_view', true],
            'maps_list' => ['maps_list', true],
            'maps_list_pagination' => ['maps_list_pagination', true],
            'unsupported_route' => ['unsupported_route', false],
        ];
    }

    public function testSeoDataForViewPage(): void
    {
        $route = new Route('map_marker_view', new Uri(''));

        $mapMetadata = new RecordViewMetadata();
        $mapMetadata->title = 'some title';
        $mapMetadata->description = 'some description';

        $mapView = new MapView();
        $mapView->metadata = $mapMetadata;
        $mapView->heading = 'some heading';

        $this->pageRouteExtension->apply($this->seoPage, new SeoContext([$route, $mapView]));

        $this->assertEquals($mapView->metadata->title, $this->seoPage->getTitle());
        $this->assertEquals($mapView->metadata->description, $this->seoPage->getDescription());
        $this->assertEquals($mapView->heading, $this->seoPage->getH1());
        $this->assertCount(1, $this->seoPage->getBreadcrumbs());
    }

    public function testSeoDataForListRoute(): void
    {
        $route = new Route('maps_list', new Uri(''));

        $this->pageRouteExtension->apply($this->seoPage, new SeoContext([$route]));

        $this->assertEquals('Карта для рыбаков - интересные места и водоемы', $this->seoPage->getTitle());
        $this->assertEquals('Рыболовные карты', $this->seoPage->getH1());
        $this->assertEquals('Спутниковая карта мира, на которой сами рыбаки отмечают интересные для рыбалки места.', $this->seoPage->getDescription());
    }
}
