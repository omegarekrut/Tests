<?php

namespace Tests\Unit\Domain\Seo\Extension\Routing;

use App\Domain\Company\Entity\Company;
use App\Domain\Company\View\CompanyView;
use App\Domain\Company\View\CompanyViewFactory;
use App\Domain\Company\View\RubricView;
use App\Domain\Seo\Extension\Routing\CompanyRouteExtension;
use App\Domain\Seo\Factory\CompanyBreadcrumbsFactory;
use App\Module\Seo\Extension\Routing\Route;
use App\Module\Seo\Extension\SeoContext;
use App\Module\Seo\TransferObject\Breadcrumb;
use App\Module\Seo\TransferObject\SeoPage;
use Laminas\Diactoros\Uri;
use Symfony\Component\Form\FormView;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Tests\Unit\TestCase;

class CompanyRouteExtensionTest extends TestCase
{
    private SeoPage $seoPage;
    private CompanyRouteExtension $pageRouteExtension;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seoPage = new SeoPage();

        $urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $urlGenerator->method('generate')
            ->willReturn('/some-link/');

        $this->pageRouteExtension = new CompanyRouteExtension(
            $this->createCompanyBreadcrumbsFactoryMock(),
            $urlGenerator,
            $this->createCompanyViewFactory()
        );
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

    protected function tearDown(): void
    {
        unset($this->seoPage, $this->pageRouteExtension);

        parent::tearDown();
    }

    public function testSeoForTidingsListRoute(): void
    {
        $route = new Route('company_list', new Uri(''));
        $this->pageRouteExtension->apply($this->seoPage, new SeoContext([$route, new FormView()]));

        $this->assertEquals('Товары и услуги для рыбалки', $this->seoPage->getTitle());
        $this->assertEquals('Товары и услуги для рыбалки', $this->seoPage->getH1());
    }

    public function testSeoForTidingsListRouteForSearch(): void
    {
        $route = new Route('company_list', new Uri(''));
        $formView = new FormView();
        $formView->vars = ['data' => json_decode(json_encode(['companySearch' => 'удочка']))];

        $this->pageRouteExtension->apply($this->seoPage, new SeoContext([$route, $formView]));

        $this->assertEquals('Товары и услуги для рыбалки - поиск по тексту: "удочка"', $this->seoPage->getTitle());
        $this->assertEquals('Товары и услуги для рыбалки - поиск по тексту: "удочка"', $this->seoPage->getH1());
    }

    public function testSeoForTidingsListRubricRoute(): void
    {
        $route = new Route('company_list_by_rubric', new Uri(''));
        $rubricView = new RubricView();
        $rubricView->name = 'Rubric name';

        $this->pageRouteExtension->apply($this->seoPage, new SeoContext([$route, new FormView(), $rubricView]));

        $this->assertEquals('Rubric name', $this->seoPage->getTitle());
        $this->assertEquals('Rubric name', $this->seoPage->getH1());
    }

    public function testSeoForTidingsListRubricRouteForSearch(): void
    {
        $route = new Route('company_list_by_rubric', new Uri(''));
        $formView = new FormView();
        $formView->vars = ['data' => json_decode(json_encode(['companySearch' => 'удочка']))];
        $rubricView = new RubricView();
        $rubricView->name = 'Rubric name';

        $this->pageRouteExtension->apply($this->seoPage, new SeoContext([$route, $formView, $rubricView]));

        $this->assertEquals('Rubric name - поиск по тексту: "удочка"', $this->seoPage->getTitle());
        $this->assertEquals('Rubric name - поиск по тексту: "удочка"', $this->seoPage->getH1());
    }

    public function testSeoDataForCreateCompanyRoute(): void
    {
        $route = $this->createConfiguredMock(Route::class, [
            'getName' => 'company_create',
        ]);

        $this->pageRouteExtension->apply($this->seoPage, new SeoContext([$route]));

        $breadcrumbs = $this->seoPage->getBreadcrumbs();

        $this->assertCount(1, $breadcrumbs);
        $this->assertEquals('Товары и услуги для рыбалки', $breadcrumbs[0]->getTitle());
    }

    /**
     * @dataProvider getRoutesForEditCompanyInformation
     */
    public function testSeoDataForEditCompanyRoutes(string $routeName): void
    {
        $route = $this->createConfiguredMock(Route::class, [
            'getName' => $routeName,
        ]);

        $this->pageRouteExtension->apply($this->seoPage, new SeoContext([$route, $this->createMock(Company::class)]));

        $breadcrumbs = $this->seoPage->getBreadcrumbs();

        $this->assertCount(2, $breadcrumbs);
        $this->assertEquals('Название компании', $breadcrumbs[0]->getTitle());
        $this->assertEquals('Товары и услуги для рыбалки', $breadcrumbs[1]->getTitle());
    }

    /**
     * @return mixed[]
     */
    public function getRoutesForEditCompanyInformation(): array
    {
        return [
            'company_edit_basic' => ['company_edit_basic'],
            'company_edit_contacts' => ['company_edit_contacts'],
            'company_edit_images' => ['company_edit_images'],
            'company_edit_description' => ['company_edit_description'],
            'company_edit_social_networks' => ['company_edit_social_networks'],
            'company_statistics_view' => ['company_statistics_view'],
            'company_subscription_control' => ['company_subscription_control'],
        ];
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
            'company_list' => ['company_list', true],
            'company_list_pagination' => ['company_list_pagination', true],
            'company_news' => ['company_news', true],
            'company_news_pagination' => ['company_news_pagination', true],
            'company_list_by_rubric' => ['company_list_by_rubric', true],
            'company_list_by_rubric_pagination' => ['company_list_by_rubric_pagination', true],

            'company_view' => ['company_view', true],

            'company_edit_basic' => ['company_edit_basic', true],
            'company_edit_contacts' => ['company_edit_contacts', true],
            'company_edit_images' => ['company_edit_images', true],
            'company_edit_description' => ['company_edit_description', true],
            'company_edit_social_networks' => ['company_edit_social_networks', true],

            'company_create' => ['company_create', true],

            'company_statistics_view' => ['company_statistics_view', true],

            'company_employees_list' => ['company_employees_list', true],
            'company_employees_add' => ['company_employees_add', true],

            'company_subscription_control' => ['company_subscription_control', true],

            'unsupported_route' => ['unsupported_route', false],
        ];
    }

    private function createCompanyViewFactory(): CompanyViewFactory
    {
        $companyView = new CompanyView();

        $mock = $this->createMock(CompanyViewFactory::class);

        $mock->method('create')
            ->willReturn($companyView);

        return $mock;
    }
}
