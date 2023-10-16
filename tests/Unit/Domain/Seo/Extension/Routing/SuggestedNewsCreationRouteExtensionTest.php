<?php

namespace Tests\Unit\Domain\Seo\Extension\Routing;

use App\Domain\Seo\Extension\Routing\SuggestedNewsCreationRouteExtension;
use App\Module\Seo\Extension\Routing\Route;
use App\Module\Seo\Extension\SeoContext;
use App\Module\Seo\TransferObject\SeoPage;
use Tests\Traits\RouteExtensionTrait;
use Tests\Unit\TestCase;

class SuggestedNewsCreationRouteExtensionTest extends TestCase
{
    use RouteExtensionTrait;

    private $pageRouteExtension;

    protected function setUp(): void
    {
        parent::setUp();

        $this->pageRouteExtension = new SuggestedNewsCreationRouteExtension(
            $this->createUrlGeneratorMock(),
            $this->createBreadcrumbsFactoryMock()
        );
    }

    public function testApplySeoForSuggestedNewsCreate(): void
    {
        $seoPage = new SeoPage();

        $this->pageRouteExtension->apply($seoPage, new SeoContext([]));

        $this->assertEquals('Предложить новость', $seoPage->getTitle());
        $this->assertEquals('Предложить новость', $seoPage->getH1());
        $this->assertCount(1, $seoPage->getBreadcrumbs());
        $this->assertEquals('Новости', $seoPage->getBreadcrumbs()[0]->getTitle());
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
            'suggested_news_create_route' => ['suggested_news_create', true],
            'suggested_news_admin_index' => ['admin_suggested_news_index', false],
        ];
    }
}
