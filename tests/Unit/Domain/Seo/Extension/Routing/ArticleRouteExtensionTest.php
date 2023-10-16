<?php

namespace Tests\Unit\Domain\Seo\Extension\Routing;

use App\Domain\Category\Entity\Category;
use App\Domain\Rating\Calculator\RecordAggregateRatingCalculator;
use App\Domain\Rating\ValueObject\RatingInfo;
use App\Domain\Record\Article\View\ArticleView;
use App\Domain\Record\Common\View\RecordViewMetadata;
use App\Domain\Seo\Extension\Routing\ArticleRouteExtension;
use App\Module\Author\View\AuthorView;
use App\Module\Seo\Extension\Routing\Route;
use App\Module\Seo\Extension\SeoContext;
use App\Module\Seo\TransferObject\SeoPage;
use Carbon\Carbon;
use Tests\Unit\TestCase;
use Laminas\Diactoros\Uri;

/**
 * @todo refactoring
 */
class ArticleRouteExtensionTest extends TestCase
{
    private SeoPage $seoPage;
    private ArticleRouteExtension $pageRouteExtension;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seoPage = new SeoPage();
        $this->pageRouteExtension = $this->createArticleRouteExtension();
    }

    private function createArticleRouteExtension(): ArticleRouteExtension
    {
        $aggregateRatingCalculatorMock = $this->createMock(RecordAggregateRatingCalculator::class);

        $aggregateRatingCalculatorMock
            ->method('calculate')
            ->willReturn('3.4');

        return new ArticleRouteExtension($aggregateRatingCalculatorMock);
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
            'article_view_route' => ['article_view', true],
            'articles_list_route' => ['articles_list', true],
            'articles_list_pagination_route' => ['articles_list_pagination', true],
            'articles_category_list_route' => ['articles_category_list', true],
            'articles_category_list_pagination_route' => ['articles_category_list_pagination', true],
            'unsupported_route' => ['unsupported_route', false],
        ];
    }

    public function testSeoForIndexPage(): void
    {
        $route = new Route('articles_list', new Uri(''));
        $category = new Category('article', 'all article content', 'article');

        $this->pageRouteExtension->apply($this->seoPage, new SeoContext([$route, $category]));

        $this->assertEquals('article', $this->seoPage->getTitle());
        $this->assertEquals('article', $this->seoPage->getH1());
        $this->assertEquals('all article content', $this->seoPage->getDescription());
    }

    public function testSeoDataApplyToPageForArticleViewRoute(): void
    {
        [$routeName, $context, $expectedSeoData] = $this->getArticleViewRouteWithSeoData();

        $route = new Route($routeName, new Uri(''));

        $this->pageRouteExtension->apply($this->seoPage, new SeoContext(array_merge($context, [$route])));

        $this->assertEquals($expectedSeoData['title'], $this->seoPage->getTitle());
        $this->assertEquals($expectedSeoData['h1'], $this->seoPage->getH1());
        $this->assertEquals($expectedSeoData['description'], $this->seoPage->getDescription());

        $this->assertNotEmpty($this->seoPage->getMicroData());

        $microdata = $this->seoPage->getMicroData();
        $this->assertEquals([
            '@context' => 'http://schema.org',
            '@type' => 'Article',
            'author' => 'username',
            'description' => $expectedSeoData['description'],
            'headline' => $expectedSeoData['title'],
            'name' => $expectedSeoData['title'],
            'datePublished' => $expectedSeoData['createdAt'],
            'dateModified' => $expectedSeoData['updatedAt'],
            'aggregateRating' => [
                '@type' => 'AggregateRating',
                'ratingValue' => '3.4',
                'ratingCount' => '0',
            ],
        ], $microdata->getDataForJson());
    }

    public function getArticleViewRouteWithSeoData(): array
    {
        $articleMetadata = new RecordViewMetadata();
        $articleMetadata->title = 'some title';
        $articleMetadata->description = 'some description';

        $articleView = new ArticleView();
        $articleView->metadata = $articleMetadata;
        $articleView->heading = 'heading';
        $articleView->createdAt = Carbon::now();
        $articleView->updatedAt = Carbon::now()->addDay();

        $articleView->author = new AuthorView();
        $articleView->author->name = 'username';

        $articleView->category = new Category('news', 'all news content', 'news');
        $articleView->htmlText = 'html text';
        $articleView->ratingInfo = $this->createMock(RatingInfo::class);

        return [
            'article_view',
            [
                $articleView,
            ],
            [
                'title' => $articleView->metadata->title,
                'h1' => $articleView->heading,
                'description' => $articleView->metadata->description,
                'createdAt' => $articleView->createdAt->format('Y-m-d\TH:i:sO'),
                'updatedAt' => $articleView->updatedAt->format('Y-m-d\TH:i:sO'),
            ],
        ];
    }
}
