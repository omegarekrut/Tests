<?php

namespace Tests\Unit\Domain\Seo\Extension\Routing;

use App\Domain\Company\View\CompanyView;
use App\Domain\Record\Common\View\RecordViewMetadata;
use App\Domain\Record\CompanyArticle\View\CompanyArticleView;
use App\Domain\Seo\Extension\Routing\CompanyArticleRouteExtension;
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

class CompanyArticleRouteExtensionTest extends TestCase
{

    use RouteExtensionTrait;

    private SeoPage $seoPage;
    private SeoRouteExtensionInterface $pageRouteExtension;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seoPage = new SeoPage();
        $this->pageRouteExtension = new CompanyArticleRouteExtension($this->createCompanyBreadcrumbsFactoryMock());
    }

    protected function tearDown(): void
    {
        unset(
            $this->seoPage,
            $this->pageRouteExtension,
        );

        parent::tearDown();
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
            'company_article_view_route' => ['company_article_view', true],
            'company_article_create_route' => ['company_article_create', true],
            'company_article_edit_route' => ['company_article_edit', true],
            'unsupported_route' => ['unsupported_route', false],
        ];
    }

    public function testSeoForViewPage(): void
    {
        $route = new Route('company_article_view', new Uri(''));
        $view = $this->createCompanyArticleView();

        $this->pageRouteExtension->apply($this->seoPage, new SeoContext([$route, $view]));

        $this->assertEquals($view->metadata->title, $this->seoPage->getTitle());
        $this->assertEquals($view->metadata->description, $this->seoPage->getDescription());
        $this->assertEquals($view->heading, $this->seoPage->getH1());

        $actualMicrodata = $this->seoPage->getMicroData()->getDataForJson();

        $this->assertEquals($view->metadata->title, $actualMicrodata['name']);
        $this->assertEquals($view->company->name, $actualMicrodata['author']);
        $this->assertEquals($view->createdAt->format('Y-m-d\TH:i:sO'), $actualMicrodata['datePublished']);

        $this->assertEquals('Товары и услуги для рыбалки', $this->seoPage->getBreadcrumbs()[1]->getTitle());
        $this->assertEquals($view->company->name, $this->seoPage->getBreadcrumbs()[0]->getTitle());
    }

    public function testApplySeoForCompanyArticleCreate(): void
    {
        $route = new Route('company_article_create', new Uri(''));
        $company = $this->createCompanyViewMock();
        $seoPage = new SeoPage();

        $this->pageRouteExtension->apply($seoPage, new SeoContext([$route, $company]));

        $this->assertEquals('Добавить запись компании', $seoPage->getTitle());
        $this->assertEquals('Добавить запись компании', $seoPage->getH1());
        $this->assertCount(2, $seoPage->getBreadcrumbs());
        $this->assertEquals('Название компании', $seoPage->getBreadcrumbs()[0]->getTitle());
        $this->assertEquals('Товары и услуги для рыбалки', $seoPage->getBreadcrumbs()[1]->getTitle());
    }

    public function testApplySeoForCompanyArticleEdit(): void
    {
        $route = new Route('company_article_edit', new Uri(''));
        $company = $this->createCompanyViewMock();
        $seoPage = new SeoPage();

        $this->pageRouteExtension->apply($seoPage, new SeoContext([$route, $company]));

        $this->assertEquals('Редактировать запись компании', $seoPage->getTitle());
        $this->assertEquals('Редактировать запись компании', $seoPage->getH1());
        $this->assertCount(2, $seoPage->getBreadcrumbs());
        $this->assertEquals('Название компании', $seoPage->getBreadcrumbs()[0]->getTitle());
        $this->assertEquals('Товары и услуги для рыбалки', $seoPage->getBreadcrumbs()[1]->getTitle());
    }

    private function createCompanyArticleView(): CompanyArticleView
    {
        $companyArticleView = new CompanyArticleView();

        $metadata = new RecordViewMetadata();
        $metadata->title = 'article title';
        $metadata->description = 'article description';

        $companyArticleView->heading = 'article heading';
        $companyArticleView->createdAt = Carbon::now();
        $companyArticleView->company = $this->createCompanyViewMock();
        $companyArticleView->metadata = $metadata;

        return $companyArticleView;
    }

    private function createCompanyViewMock(): CompanyView
    {
        $companyView = new CompanyView();
        $companyView->name = 'Название компании';
        $companyView->viewUrl = '/company-slug/company-short-uuid/';

        return $companyView;
    }
}
