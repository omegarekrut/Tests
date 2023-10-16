<?php

namespace Tests\Unit\Domain\Seo\Factory;

use App\Domain\Company\View\CompanyView;
use App\Domain\Seo\Extension\CustomInfoByUriExtension;
use App\Domain\Seo\Factory\CompanyBreadcrumbsFactory;
use App\Module\Seo\Factory\BreadcrumbsFactory;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Tests\Unit\TestCase;

class CompanyBreadcrumbsFactoryTest extends TestCase
{
    public function testGenerateBreadcrumbsForCompanyListPage(): void
    {
        $expectedListUri = '/companies/';

        $uri = $this->createMock(UrlGeneratorInterface::class);
        $uri
            ->expects($this->once())
            ->method('generate')
            ->willReturn($expectedListUri);

        $companyBreadcrumbsFactory = new CompanyBreadcrumbsFactory(
            $this->getBreadcrumbsFactory(),
            $uri
        );

        $breadcrumb = $companyBreadcrumbsFactory->getBreadcrumbForCompanyList();

        $this->assertEquals('Товары и услуги для рыбалки', $breadcrumb->getTitle());
        $this->assertEquals($expectedListUri, (string) $breadcrumb->getUri());
    }

    public function testGenerateBreadcrumbsForCompanyViewPage(): void
    {
        $companyView = new CompanyView();
        $companyView->name = 'название компании';
        $companyView->viewUrl = '/some-path/slug/';

        $companyBreadcrumbsFactory = new CompanyBreadcrumbsFactory(
            $this->getBreadcrumbsFactory(),
            $this->createMock(UrlGeneratorInterface::class)
        );

        $breadcrumb = $companyBreadcrumbsFactory->getBreadcrumbForCompanyViewPage($companyView);

        $this->assertEquals($companyView->name, $breadcrumb->getTitle());
        $this->assertEquals($companyView->viewUrl, (string) $breadcrumb->getUri());
    }

    private function getBreadcrumbsFactory(): BreadcrumbsFactory
    {
        return new BreadcrumbsFactory(
            $this->createMock(CustomInfoByUriExtension::class)
        );
    }
}
