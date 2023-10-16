<?php

namespace Tests\Unit\Domain\Seo\Extension\Routing;

use App\Domain\Seo\Extension\Routing\ArticleCreationRouteExtension;
use App\Module\Seo\Extension\Routing\Route;
use App\Module\Seo\Extension\SeoContext;
use App\Module\Seo\TransferObject\SeoPage;
use Laminas\Diactoros\Uri;
use Tests\Traits\RouteExtensionTrait;
use Tests\Unit\TestCase;

class ArticleCreationRouteExtensionTest extends TestCase
{
    use RouteExtensionTrait;

    private $pageRouteExtension;

    protected function setUp(): void
    {
        parent::setUp();

        $this->pageRouteExtension = new ArticleCreationRouteExtension(
            $this->createUrlGeneratorMock(),
            $this->createBreadcrumbsFactoryMock()
        );
    }

    public function testApplySeoForArticleCreate(): void
    {
        $route = new Route('article_create', new Uri(''));
        $seoPage = new SeoPage();

        $this->pageRouteExtension->apply($seoPage, new SeoContext([$route]));

        $this->assertEquals('Добавить запись', $seoPage->getTitle());
        $this->assertEquals('Добавить запись', $seoPage->getH1());
        $this->assertCount(1, $seoPage->getBreadcrumbs());
        $this->assertEquals('Записи', $seoPage->getBreadcrumbs()[0]->getTitle());
    }

    public function testApplySeoForArticleEdit(): void
    {
        $route = new Route('article_edit', new Uri(''));
        $seoPage = new SeoPage();

        $this->pageRouteExtension->apply($seoPage, new SeoContext([$route]));

        $this->assertEquals('Редактировать запись', $seoPage->getTitle());
        $this->assertEquals('Редактировать запись', $seoPage->getH1());
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
            'article_create_route' => ['article_create', true],
            'article_edit_route' => ['article_edit', true],
            'unsupported_route' => ['unsupported_route', false],
        ];
    }
}
