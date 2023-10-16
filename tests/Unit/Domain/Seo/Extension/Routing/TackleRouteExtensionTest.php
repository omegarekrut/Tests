<?php

namespace Tests\Unit\Domain\Seo\Extension\Routing;

use App\Domain\Category\Entity\Category;
use App\Domain\Record\Common\View\RecordViewMetadata;
use App\Domain\Record\Tackle\Entity\TackleBrand;
use App\Domain\Record\Tackle\View\TackleView;
use App\Domain\Seo\Extension\Routing\TackleRouteExtension;
use App\Module\Seo\Extension\Routing\Route;
use App\Module\Seo\Extension\SeoContext;
use App\Module\Seo\TransferObject\SeoPage;
use Laminas\Diactoros\Uri;
use Tests\Traits\RouteExtensionTrait;
use Tests\Unit\TestCase;

class TackleRouteExtensionTest extends TestCase
{
    use RouteExtensionTrait;

    /** @var SeoPage */
    private $seoPage;
    /** @var TackleRouteExtension */
    private $registrationRouteExtension;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seoPage = new SeoPage();
        $this->registrationRouteExtension = new TackleRouteExtension($this->createBreadcrumbsFactoryMock(), $this->createUrlGeneratorMock());
    }

    public function testSeoDataForViewPage(): void
    {
        $route = new Route('tackle_view', new Uri(''));

        $tidingMetadata = new RecordViewMetadata();
        $tidingMetadata->title = 'some title';
        $tidingMetadata->description = 'some description';

        $tackleView = new TackleView();
        $tackleView->metadata = $tidingMetadata;
        $tackleView->heading = 'some heading';

        $this->registrationRouteExtension->apply($this->seoPage, new SeoContext([$route, $tackleView]));

        $this->assertEquals($tackleView->metadata->title, $this->seoPage->getTitle());
        $this->assertEquals($tackleView->metadata->description, $this->seoPage->getDescription());
        $this->assertEquals($tackleView->heading, $this->seoPage->getH1());
    }

    public function testSeoForTackleListRoute(): void
    {
        $route = new Route('tackle_list', new Uri(''));

        $this->registrationRouteExtension->apply($this->seoPage, new SeoContext([$route]));

        $this->assertEquals('Отзывы о снастях', $this->seoPage->getTitle());
        $this->assertEquals('Отзывы о снастях', $this->seoPage->getH1());
    }

    /**
     * @dataProvider supportedRouteDataProvider
     */
    public function testSupportedRoute(string $routeName, bool $isSupport): void
    {
        $route = new Route($routeName, new Uri(''));

        $this->assertEquals($isSupport, $this->registrationRouteExtension->isSupported($route));
    }

    /**
     * @dataProvider tackleCategoryListRouteProvider
     */
    public function testSeoForTackleCategoryListRoute(string $routeName): void
    {
        $route = new Route($routeName, new Uri(''));
        $category = new Category('Some Title', 'descr', 'slug');

        $this->registrationRouteExtension->apply($this->seoPage, new SeoContext([$route, $category]));

        $this->assertEquals('Some Title', $this->seoPage->getTitle());
        $this->assertEquals('Some Title', $this->seoPage->getH1());
    }

    /**
     * @dataProvider tackleBrandListRouteProvider
     */
    public function testSeoForTackleBrandListRoute(string $routeName): void
    {
        $route = new Route($routeName, new Uri(''));
        $tackleBrand = new TackleBrand('brand title');

        $this->registrationRouteExtension->apply($this->seoPage, new SeoContext([$route, $tackleBrand]));

        $this->assertEquals('Отзывы о снастях brand title', $this->seoPage->getTitle());
        $this->assertEquals('Отзывы о снастях brand title', $this->seoPage->getH1());
    }

    /**
     * @dataProvider tackleCategoryBrandListRouteProvider
     */
    public function testSeoForTackleCategoryBrandListRoute(string $routeName): void
    {
        $route = new Route($routeName, new Uri(''));
        $category = new Category('Some category Title', 'descr', 'slug');
        $tackleBrand = new TackleBrand('brand title');

        $this->registrationRouteExtension->apply($this->seoPage, new SeoContext([$route, $tackleBrand, $category]));

        $this->assertEquals('Some category Title brand title', $this->seoPage->getTitle());
        $this->assertEquals('Some category Title brand title', $this->seoPage->getH1());
    }

    /**
     * @return mixed[]
     */
    public function supportedRouteDataProvider(): array
    {
        return [
            ['invalid_route', false],
            ['tackle_view', true],
            ['tackle_list', true],
            ['tackle_brand_list', true],
            ['tackle_brand_list_pagination', true],
            ['tackle_category_brand_list', true],
            ['tackle_category_brand_list_pagination', true],
            ['tackle_sub_category_brand_list', true],
            ['tackle_sub_category_brand_list_pagination', true],
            ['tackle_category_list', true],
            ['tackle_category_list_pagination', true],
            ['tackle_sub_category_list', true],
            ['tackle_sub_category_list_pagination', true],
        ];
    }

    /**
     * @return string[]
     */
    public function tackleCategoryListRouteProvider(): array
    {
        return [
            ['tackle_category_list'],
            ['tackle_category_list_pagination'],
            ['tackle_sub_category_list'],
            ['tackle_sub_category_list_pagination'],
        ];
    }

    /**
     * @return string[]
     */
    public function tackleBrandListRouteProvider(): array
    {
        return [
            ['tackle_brand_list'],
            ['tackle_brand_list_pagination'],
        ];
    }

    /**
     * @return string[]
     */
    public function tackleCategoryBrandListRouteProvider(): array
    {
        return [
            ['tackle_category_brand_list'],
            ['tackle_category_brand_list_pagination'],
            ['tackle_sub_category_brand_list'],
            ['tackle_sub_category_brand_list_pagination'],
        ];
    }
}
