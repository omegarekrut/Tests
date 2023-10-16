<?php

namespace Tests\Unit\Domain\Seo\Extension\Routing;

use App\Domain\Seo\Extension\Routing\TidingsCreationRouteExtension;
use App\Module\Seo\Extension\Routing\Route;
use App\Module\Seo\Extension\SeoContext;
use App\Module\Seo\TransferObject\SeoPage;
use Laminas\Diactoros\Uri;
use Tests\Traits\RouteExtensionTrait;
use Tests\Unit\TestCase;

class TidingsCreationRouteExtensionTest extends TestCase
{
    use RouteExtensionTrait;

    private $pageRouteExtension;

    protected function setUp(): void
    {
        parent::setUp();

        $this->pageRouteExtension = new TidingsCreationRouteExtension(
            $this->createUrlGeneratorMock(),
            $this->createBreadcrumbsFactoryMock()
        );
    }

    public function testApplySeoForTidingsCreate(): void
    {
        $route = new Route('tidings_create', new Uri(''));
        $seoPage = new SeoPage();

        $this->pageRouteExtension->apply($seoPage, new SeoContext([$route]));

        $this->assertEquals('Добавить весть с водоема', $seoPage->getTitle());
        $this->assertEquals('Добавить весть с водоема', $seoPage->getH1());
        $this->assertCount(1, $seoPage->getBreadcrumbs());
        $this->assertEquals('Вести с водоемов', $seoPage->getBreadcrumbs()[0]->getTitle());
    }

    public function testApplySeoForTidingsEdit(): void
    {
        $route = new Route('tidings_edit', new Uri(''));
        $seoPage = new SeoPage();

        $this->pageRouteExtension->apply($seoPage, new SeoContext([$route]));

        $this->assertEquals('Редактировать весть с водоема', $seoPage->getTitle());
        $this->assertEquals('Редактировать весть с водоема', $seoPage->getH1());
        $this->assertCount(1, $seoPage->getBreadcrumbs());
        $this->assertEquals('Вести с водоемов', $seoPage->getBreadcrumbs()[0]->getTitle());
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
            'tidings_create_route' => ['tidings_create', true],
            'tidings_edit_route' => ['tidings_edit', true],
            'unsupported_route' => ['unsupported_route', false],
        ];
    }
}
