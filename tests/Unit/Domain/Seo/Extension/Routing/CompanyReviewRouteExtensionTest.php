<?php

namespace Tests\Unit\Domain\Seo\Extension\Routing;

use App\Domain\Company\View\CompanyView;
use App\Domain\Record\Common\View\RecordViewMetadata;
use App\Domain\Record\CompanyReview\View\CompanyReviewView;
use App\Domain\Seo\Extension\Routing\CompanyReviewRouteExtension;
use App\Domain\Seo\Factory\CompanyBreadcrumbsFactory;
use App\Module\Seo\Extension\Routing\Route;
use App\Module\Seo\Extension\Routing\SeoRouteExtensionInterface;
use App\Module\Seo\Extension\SeoContext;
use App\Module\Seo\TransferObject\Breadcrumb;
use App\Module\Seo\TransferObject\SeoPage;
use Carbon\Carbon;
use Laminas\Diactoros\Uri;
use Tests\Traits\RouteExtensionTrait;
use Tests\Unit\TestCase;

class CompanyReviewRouteExtensionTest extends TestCase
{
    use RouteExtensionTrait;

    private SeoPage $seoPage;
    private SeoRouteExtensionInterface $pageRouteExtension;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seoPage = new SeoPage();
        $this->pageRouteExtension = new CompanyReviewRouteExtension($this->createCompanyBreadcrumbsFactoryMock());
    }

    private function createCompanyBreadcrumbsFactoryMock(): CompanyBreadcrumbsFactory
    {
        $mock = $this->createMock(CompanyBreadcrumbsFactory::class);

        $mock->method('getBreadcrumbForCompanyList')
            ->willReturn(new Breadcrumb('Товары и услуги для рыбалки', new Uri('/companies')));

        $mock->method('getBreadcrumbForCompanyViewPage')
            ->willReturn(new Breadcrumb('Название компании', new Uri('/company/slug')));

        return $mock;
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
            'company_review_view_route' => ['company_review_view', true],
            'company_review_create_route' => ['company_review_create', true],
            'company_review_edit_route' => ['company_review_edit', true],
            'unsupported_route' => ['unsupported_route', false],
        ];
    }

    public function testSeoForViewPage(): void
    {
        $route = new Route('company_review_view', new Uri(''));
        $view = $this->createCompanyReviewView();

        $this->pageRouteExtension->apply($this->seoPage, new SeoContext([$route, $view]));

        $this->assertEquals($view->metadata->title, $this->seoPage->getTitle());
        $this->assertEquals($view->metadata->description, $this->seoPage->getDescription());
        $this->assertEquals($view->heading, $this->seoPage->getH1());

        $actualMicrodata = $this->seoPage->getMicroData()->getDataForJson();

        $this->assertEquals($view->metadata->title, $actualMicrodata['name']);
        $this->assertEquals($view->createdAt->format('Y-m-d\TH:i:sO'), $actualMicrodata['datePublished']);

        $this->assertEquals('Товары и услуги для рыбалки', $this->seoPage->getBreadcrumbs()[1]->getTitle());
        $this->assertEquals($view->company->name, $this->seoPage->getBreadcrumbs()[0]->getTitle());
    }

    public function testApplySeoForCompanyReviewCreate(): void
    {
        $route = new Route('company_review_create', new Uri(''));
        $company = $this->createCompanyViewMock();
        $seoPage = new SeoPage();

        $this->pageRouteExtension->apply($seoPage, new SeoContext([$route, $company]));

        $this->assertEquals('Добавить отзыв на компанию Название компании', $seoPage->getTitle());
        $this->assertEquals('Добавить отзыв на компанию Название компании', $seoPage->getH1());
        $this->assertCount(2, $seoPage->getBreadcrumbs());
        $this->assertEquals('Название компании', $seoPage->getBreadcrumbs()[0]->getTitle());
        $this->assertEquals('Товары и услуги для рыбалки', $seoPage->getBreadcrumbs()[1]->getTitle());
    }

    public function testApplySeoForCompanyReviewEdit(): void
    {
        $route = new Route('company_review_edit', new Uri(''));
        $company = $this->createCompanyViewMock();
        $seoPage = new SeoPage();

        $this->pageRouteExtension->apply($seoPage, new SeoContext([$route, $company]));

        $this->assertCount(2, $seoPage->getBreadcrumbs());
        $this->assertEquals('Название компании', $seoPage->getBreadcrumbs()[0]->getTitle());
        $this->assertEquals('Товары и услуги для рыбалки', $seoPage->getBreadcrumbs()[1]->getTitle());
    }

    private function createCompanyReviewView(): CompanyReviewView
    {
        $companyReviewView = new CompanyReviewView();

        $metadata = new RecordViewMetadata();
        $metadata->title = 'review title';
        $metadata->description = 'review description';

        $companyReviewView->heading = 'review heading';
        $companyReviewView->createdAt = Carbon::now();
        $companyReviewView->company = $this->createCompanyViewMock();
        $companyReviewView->metadata = $metadata;

        return $companyReviewView;
    }

    private function createCompanyViewMock(): CompanyView
    {
        $companyView = new CompanyView();
        $companyView->name = 'Название компании';
        $companyView->viewUrl = '/company-slug/company-short-uuid/';

        return $companyView;
    }
}
