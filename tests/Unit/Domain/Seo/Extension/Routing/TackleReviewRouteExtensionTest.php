<?php

namespace Tests\Unit\Domain\Seo\Extension\Routing;

use App\Domain\Record\Common\View\RecordViewMetadata;
use App\Domain\Record\Tackle\View\TackleReviewView;
use App\Domain\Seo\Extension\Routing\TackleReviewRouteExtension;
use App\Module\Seo\Extension\Routing\Route;
use App\Module\Seo\Extension\SeoContext;
use App\Module\Seo\TransferObject\SeoPage;
use Tests\Traits\RouteExtensionTrait;
use Tests\Unit\TestCase;
use Laminas\Diactoros\Uri;

class TackleReviewRouteExtensionTest extends TestCase
{
    use RouteExtensionTrait;

    /** @var SeoPage */
    private $seoPage;
    /** @var TackleReviewRouteExtension */
    private $tackleReviewRouteExtension;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seoPage = new SeoPage();
        $this->tackleReviewRouteExtension = new TackleReviewRouteExtension($this->createBreadcrumbsFactoryMock());
    }

    public function testSeoDataForViewPage(): void
    {
        $route = new Route('tackle_review_view', new Uri(''));

        $tidingMetadata = new RecordViewMetadata();
        $tidingMetadata->title = 'some title';
        $tidingMetadata->description = 'some description';

        $tackleReviewView = new TackleReviewView();
        $tackleReviewView->metadata = $tidingMetadata;
        $tackleReviewView->heading = 'some heading';
        $tackleReviewView->tackleHeading = 'Tackle name';
        $tackleReviewView->tackleViewUrl = new Uri('/tackle/link');

        $this->tackleReviewRouteExtension->apply($this->seoPage, new SeoContext([$route, $tackleReviewView]));

        $this->assertEquals($tackleReviewView->metadata->title, $this->seoPage->getTitle());
        $this->assertEquals($tackleReviewView->metadata->description, $this->seoPage->getDescription());
        $this->assertEquals($tackleReviewView->heading, $this->seoPage->getH1());
        $this->assertCount(1, $this->seoPage->getBreadcrumbs());

        $breadcrumbs = $this->seoPage->getBreadcrumbs();
        $this->assertEquals('Tackle name - отзывы', $breadcrumbs[0]->getTitle());
        $this->assertEquals('/tackle/link', $breadcrumbs[0]->getUri());
    }

    /**
     * @dataProvider supportedRouteDataProvider
     */
    public function testSupportedRoute(string $routeName, bool $isSupport): void
    {
        $route = new Route($routeName, new Uri(''));

        $this->assertEquals($isSupport, $this->tackleReviewRouteExtension->isSupported($route));
    }

    public function supportedRouteDataProvider(): \Generator
    {
        yield [
            'invalid_route',
            false,
        ];

        yield [
            'tackle_review_view',
            true,
        ];
    }
}
