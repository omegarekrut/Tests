<?php

namespace Tests\Unit\Domain\Seo\Extension\Routing;

use App\Domain\Seo\Extension\Routing\GalleryCreationRouteExtension;
use App\Module\Seo\Extension\Routing\Route;
use App\Module\Seo\Extension\SeoContext;
use App\Module\Seo\TransferObject\SeoPage;
use Laminas\Diactoros\Uri;
use Tests\Traits\RouteExtensionTrait;
use Tests\Unit\TestCase;

class GalleryCreationRouteExtensionTest extends TestCase
{
    use RouteExtensionTrait;

    private $pageRouteExtension;

    protected function setUp(): void
    {
        parent::setUp();

        $this->pageRouteExtension = new GalleryCreationRouteExtension(
            $this->createUrlGeneratorMock(),
            $this->createBreadcrumbsFactoryMock()
        );
    }

    public function testApplySeoForGalleryCreate(): void
    {
        $route = new Route('gallery_create', new Uri(''));
        $seoPage = new SeoPage();

        $this->pageRouteExtension->apply($seoPage, new SeoContext([$route]));

        $this->assertEquals('Добавить фотографии', $seoPage->getTitle());
        $this->assertEquals('Добавить фотографии', $seoPage->getH1());
        $this->assertCount(1, $seoPage->getBreadcrumbs());
        $this->assertEquals('Рыболовная фотогалерея', $seoPage->getBreadcrumbs()[0]->getTitle());
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

    /**
     * @return mixed[]
     */
    public function getRoutesForCheckSupports(): array
    {
        return [
            'gallery_create_route' => ['gallery_create', true],
            'unsupported_route' => ['unsupported_route', false],
        ];
    }
}
