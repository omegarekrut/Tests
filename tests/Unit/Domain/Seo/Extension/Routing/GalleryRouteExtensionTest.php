<?php

namespace Tests\Unit\Domain\Seo\Extension\Routing;

use App\Domain\Category\Entity\Category;
use App\Domain\Record\Common\View\RecordViewMetadata;
use App\Domain\Record\Gallery\View\GalleryView;
use App\Domain\Seo\Extension\Routing\GalleryRouteExtension;
use App\Module\Seo\Extension\Routing\Route;
use App\Module\Seo\Extension\SeoContext;
use App\Module\Seo\TransferObject\SeoPage;
use Generator;
use Symfony\Component\Form\FormView;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Tests\Traits\RouteExtensionTrait;
use Tests\Unit\TestCase;
use Laminas\Diactoros\Uri;

/**
 * @group seo
 */
class GalleryRouteExtensionTest extends TestCase
{
    use RouteExtensionTrait;

    private SeoPage $seoPage;
    private GalleryRouteExtension $pageRouteExtension;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seoPage = new SeoPage();

        $urlGenerator = $this->createMock(UrlGenerator::class);
        $urlGenerator->method('generate')
            ->willReturn('/some-link/');

        $this->pageRouteExtension = new GalleryRouteExtension($urlGenerator, $this->createConfiguredVisitorMock());
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
        yield 'gallery_list' => ['gallery_list', true];

        yield 'gallery_list_by_region' => ['gallery_list_by_region', true];

        yield 'gallery_list_pagination' => ['gallery_list_pagination', true];

        yield 'gallery_category_list' => ['gallery_category_list', true];

        yield 'gallery_category_list_pagination' => ['gallery_category_list_pagination', true];

        yield 'gallery_view' => ['gallery_view', true];

        yield 'gallery_category_view' => ['gallery_category_view', true];

        yield 'unsupported_route' => ['unsupported_route', false];
    }

    public function testSeoForGalleryListByRegionRoute(): void
    {
        $route = new Route('gallery_list_by_region', new Uri(''));
        $this->pageRouteExtension->apply($this->seoPage, new SeoContext([$route, new FormView()]));

        $this->assertEquals('Рыболовная фотогалерея - Новосибирск', $this->seoPage->getTitle());
        $this->assertEquals('Рыболовная фотогалерея - Новосибирск', $this->seoPage->getH1());
    }

    /**
     * @dataProvider getListRoutes
     */
    public function testSeoDataForListRoute(string $routeName): void
    {
        $route = new Route($routeName, new Uri(''));

        $category = new Category('Some category name', 'Some category description', 'some-category');

        $this->pageRouteExtension->apply($this->seoPage, new SeoContext([$route, $category]));

        $this->assertEquals($category->getTitle(), $this->seoPage->getTitle());
        $this->assertEquals($category->getTitle(), $this->seoPage->getH1());
        $this->assertEquals($category->getDescription(), $this->seoPage->getDescription());
    }

    public function getListRoutes(): Generator
    {
        yield 'gallery_list' => ['gallery_list'];

        yield 'gallery_list_pagination' => ['gallery_list_pagination'];

        yield 'gallery_category_list' => ['gallery_category_list'];

        yield 'gallery_category_list_pagination' => ['gallery_category_list_pagination'];
    }

    /**
     * @dataProvider getViewRoutes
     */
    public function testSeoDataForViewRoute(string $routeName): void
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
    }

    public function getViewRoutes(): Generator
    {
        yield 'gallery_view' => ['gallery_view'];

        yield 'gallery_category_view' => ['gallery_category_view'];
    }
}
