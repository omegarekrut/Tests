<?php

namespace Tests\Unit\Domain\Seo\Extension\Routing;

use App\Domain\Category\Entity\Category;
use App\Domain\Record\Common\View\RecordViewMetadata;
use App\Domain\Record\Gallery\View\GalleryView;
use App\Domain\Seo\Extension\Routing\GalleryBestRouteExtension;
use App\Module\Seo\Extension\Routing\Route;
use App\Module\Seo\Extension\SeoContext;
use App\Module\Seo\TransferObject\SeoPage;
use Generator;
use Tests\Traits\RouteExtensionTrait;
use Tests\Unit\TestCase;
use Laminas\Diactoros\Uri;

/**
 * @group seo
 */
class GalleryBestRouteExtensionTest extends TestCase
{
    use RouteExtensionTrait;

    private SeoPage $seoPage;
    private GalleryBestRouteExtension $pageRouteExtension;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seoPage = new SeoPage();

        $this->pageRouteExtension = new GalleryBestRouteExtension($this->createBreadcrumbsFactoryMock(), $this->createUrlGeneratorMock());
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

    public function getRoutesForCheckSupports(): Generator
    {
        yield 'gallery_best' => ['gallery_best', true];

        yield 'gallery_best_pagination' => ['gallery_best_pagination', true];

        yield 'gallery_best30' => ['gallery_best30', true];

        yield 'gallery_best30_pagination' => ['gallery_best30_pagination', true];

        yield 'gallery_best30_view' => ['gallery_best30_view', true];

        yield 'gallery_best_view' => ['gallery_best_view', true];

        yield 'unsupported_route' => ['unsupported_route', false];
    }

    /**
     * @dataProvider getListRoutes
     *
     * @param string[] $expectedMetaData
     */
    public function testSeoDataForListRoute(string $routeName, array $expectedMetaData): void
    {
        $route = new Route($routeName, new Uri(''));

        $category = new Category('Some category name', 'Some category description', 'some-category');

        $this->pageRouteExtension->apply($this->seoPage, new SeoContext([$route, $category]));

        $this->assertEquals($expectedMetaData['title'], $this->seoPage->getTitle());
        $this->assertEquals($expectedMetaData['h1'], $this->seoPage->getH1());
        $this->assertEquals($expectedMetaData['description'], $this->seoPage->getDescription());

        $breadcrumbs = $this->seoPage->getBreadcrumbs();
        $this->assertCount(1, $breadcrumbs);
        $this->assertEquals('Рыболовная фотогалерея', reset($breadcrumbs)->getTitle());
    }

    public function getListRoutes(): Generator
    {
        $seoMetaDataForBest = [
            'title' => 'Лучшие фотографии с рыбалки',
            'h1' => 'Лучшие фотографии с рыбалки',
            'description' => 'Фотографии с рыбалки: пойманные трофеи, кулинарные шедевры, фотографии природы, животных, птиц - всего, что сопровождает человека на рыбалке.',
        ];

        yield 'gallery_best' => ['gallery_best', $seoMetaDataForBest];

        yield 'gallery_best_pagination' => ['gallery_best_pagination', $seoMetaDataForBest];

        $seoMetaDataForBest30 = [
            'title' => 'Лучшие за 30 дней',
            'h1' => 'Лучшие за 30 дней',
            'description' => 'Фотографии с рыбалки: пойманные трофеи, кулинарные шедевры, фотографии природы, животных, птиц - всего, что сопровождает человека на рыбалке.',
        ];

        yield 'gallery_best30' => ['gallery_best30', $seoMetaDataForBest30];

        yield 'gallery_best30_pagination' => ['gallery_best30_pagination', $seoMetaDataForBest30];
    }

    /**
     * @dataProvider getViewRoutes
     */
    public function testSeoDataForViewRoute(string $routeName, string $expectedSectionBreadcrumbsName): void
    {
        $route = new Route($routeName, new Uri(''));

        $galleryView = new GalleryView();
        $galleryView->id = 1;
        $galleryView->heading = 'name gallery';
        $galleryView->metadata = new RecordViewMetadata();
        $galleryView->metadata->title = 'Gallery title';
        $galleryView->metadata->description = 'some description for gallery';

        $this->pageRouteExtension->apply($this->seoPage, new SeoContext([$route, $galleryView]));

        $this->assertEquals($galleryView->metadata->title, $this->seoPage->getTitle());
        $this->assertEquals($galleryView->heading, $this->seoPage->getH1());
        $this->assertEquals($galleryView->metadata->description, $this->seoPage->getDescription());
        $this->assertEquals('/some-link/', $this->seoPage->getCanonicalLink());

        $breadcrumbs = $this->seoPage->getBreadcrumbs();
        $this->assertCount(2, $breadcrumbs);
        $this->assertEquals($expectedSectionBreadcrumbsName, reset($breadcrumbs)->getTitle());
    }

    public function getViewRoutes(): Generator
    {
        yield 'gallery_best30_view' => ['gallery_best30_view', 'Лучшие за 30 дней'];

        yield 'gallery_best_view' => ['gallery_best_view', 'Лучшие за все время'];
    }
}
