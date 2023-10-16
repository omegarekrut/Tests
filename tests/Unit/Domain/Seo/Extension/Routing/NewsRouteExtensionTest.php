<?php

namespace Tests\Unit\Domain\Seo\Extension\Routing;

use App\Domain\Category\Entity\Category;
use App\Domain\Record\Common\View\RecordViewMetadata;
use App\Domain\Record\News\View\NewsView;
use App\Domain\Seo\Extension\Routing\NewsRouteExtension;
use App\Module\Author\View\AuthorView;
use App\Module\Seo\Extension\Routing\Route;
use App\Module\Seo\Extension\SeoContext;
use App\Module\Seo\TransferObject\SeoPage;
use Carbon\Carbon;
use Tests\Traits\RouteExtensionTrait;
use Tests\Unit\TestCase;
use Laminas\Diactoros\Uri;

/**
 * @todo refactoring
 */
class NewsRouteExtensionTest extends TestCase
{
    use RouteExtensionTrait;

    /** @var SeoPage */
    private $seoPage;
    /** @var NewsRouteExtension */
    private $pageRouteExtension;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seoPage = new SeoPage();
        $this->pageRouteExtension = new NewsRouteExtension($this->createBreadcrumbsFactoryMock(), $this->createUrlGeneratorMock());
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
            'news_view_route' => ['news_view', true],
            'news_list_route' => ['news_list', true],
            'news_list_pagination_route' => ['news_list_pagination', true],
            'unsupported_route' => ['unsupported_route', false],
        ];
    }

    public function testSeoForNewsListRoute(): void
    {
        $route = new Route('news_list', new Uri(''));

        $this->pageRouteExtension->apply($this->seoPage, new SeoContext([$route]));

        $this->assertEquals('Новости', $this->seoPage->getTitle());
        $this->assertEquals('Новости', $this->seoPage->getH1());
    }

    /**
     * @dataProvider getRoutesWithSeoData
     */
    public function testSeoDataApplyToPage(string $routeName, array $context, array $expectedSeoData): void
    {
        $route = new Route($routeName, new Uri(''));

        $this->pageRouteExtension->apply($this->seoPage, new SeoContext(array_merge($context, [$route])));

        $this->assertEquals($expectedSeoData['title'], $this->seoPage->getTitle());
        $this->assertEquals($expectedSeoData['h1'], $this->seoPage->getH1());
        $this->assertEquals($expectedSeoData['description'], $this->seoPage->getDescription());
        $this->assertCount(1, $this->seoPage->getBreadcrumbs());

        $this->assertNotEmpty($this->seoPage->getMicroData());

        $microdata = $this->seoPage->getMicroData();
        $this->assertEquals([
            '@context' => 'http://schema.org',
            '@type' => 'NewsArticle',
            'author' => 'username',
            'description' => $expectedSeoData['description'],
            'headline' => $expectedSeoData['title'],
            'name' => $expectedSeoData['title'],
            'datePublished' => $expectedSeoData['createdAt'],
            'dateModified' => $expectedSeoData['updatedAt'],

        ], $microdata->getDataForJson());
    }

    public function getRoutesWithSeoData(): array
    {
        $newsMetadata = new RecordViewMetadata();
        $newsMetadata->title = 'some title';
        $newsMetadata->description = 'some description';

        $newsView = new NewsView();
        $newsView->metadata = $newsMetadata;
        $newsView->heading = 'heading';
        $newsView->createdAt = Carbon::now();
        $newsView->updatedAt = Carbon::now()->addDay();

        $newsView->author = new AuthorView();
        $newsView->author->name = 'username';

        $newsView->category = new Category('news', 'all news content', 'news');
        $newsView->htmlText = 'html text';

        return [
            'news_view' => [
                'news_view',
                [
                    $newsView,
                ],
                [
                    'title' => $newsView->metadata->title,
                    'h1' => $newsView->heading,
                    'description' => $newsView->metadata->description,
                    'createdAt' => $newsView->createdAt->format('Y-m-d\TH:i:sO'),
                    'updatedAt' => $newsView->updatedAt->format('Y-m-d\TH:i:sO'),
                ],
            ],
        ];
    }
}
