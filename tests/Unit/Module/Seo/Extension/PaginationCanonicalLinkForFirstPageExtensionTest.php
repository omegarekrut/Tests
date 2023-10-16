<?php

namespace Tests\Unit\Module\Seo\Extension;

use App\Module\Seo\Extension\CanonicalLink\PaginationCanonicalLinkForFirstPageExtension;
use App\Module\Seo\Extension\SeoContext;
use App\Module\Seo\TransferObject\SeoPage;
use Knp\Bundle\PaginatorBundle\Pagination\SlidingPagination;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Tests\Unit\LoggerMock;
use Tests\Unit\TestCase;
use Laminas\Diactoros\Uri;

/**
 * @group seo
 */
class PaginationCanonicalLinkForFirstPageExtensionTest extends TestCase
{
    /**
     * @dataProvider getContextForNotSupportedRoute
     */
    public function testNotSupportedRoute($contextData): void
    {
        $expectedSeoPage = $seoPage = new SeoPage();

        $extension = new PaginationCanonicalLinkForFirstPageExtension($this->createMock(UrlGeneratorInterface::class));
        $extension->apply($seoPage, new SeoContext($contextData));

        $this->assertEquals($expectedSeoPage, $seoPage);
    }

    public function getContextForNotSupportedRoute(): \Generator
    {
        yield [
            [],
        ];

        $paginatorForSecondPage = new SlidingPagination([]);
        $paginatorForSecondPage->setCurrentPageNumber(2);
        yield [
            [$paginatorForSecondPage],
        ];

        $paginatorWithCustomParameter = new SlidingPagination([]);
        $paginatorWithCustomParameter->setCustomParameters(['someParameter' => 'value']);
        yield [
            [$paginatorWithCustomParameter],
        ];

        $paginatorWithoutCustomParameterForFirstPage = new SlidingPagination([]);
        $paginatorWithoutCustomParameterForFirstPage->setCurrentPageNumber(1);
        yield [
            [$paginatorWithoutCustomParameterForFirstPage],
        ];

        $paginatorWithCustomParameterForFirstPage = new SlidingPagination([]);
        $paginatorWithCustomParameterForFirstPage->setCurrentPageNumber(1);
        $paginatorWithCustomParameterForFirstPage->setCustomParameters(['someParameter' => 'value']);
        yield [
            [$paginatorWithCustomParameterForFirstPage],
        ];

        $paginatorWithCustomParameterForSecondPage = new SlidingPagination([]);
        $paginatorWithCustomParameterForSecondPage->setCurrentPageNumber(2);
        $paginatorWithCustomParameterForSecondPage->setCustomParameters(['someParameter' => 'value']);
        yield [
            [$paginatorWithCustomParameterForSecondPage],
        ];
    }

    public function testCanonicalLinkForFirstPage(): void
    {
        $paginator = new SlidingPagination([]);
        $paginator->setCurrentPageNumber(1);
        $paginator->setCustomParameters(['firstPageRoute' => 'simple_first_page_route']);

        $seoPage = new SeoPage();
        $seoPage->setCanonicalLink(new Uri('/some/route/page1/'));

        $extension = new PaginationCanonicalLinkForFirstPageExtension($this->getUrlGenerator());
        $extension->apply($seoPage, new SeoContext([$paginator]));

        $this->assertEquals('/some_route/first_page/', $seoPage->getCanonicalLink()->getPath());
    }

    private function getUrlGenerator(): UrlGeneratorInterface
    {
        $routeCollection = new RouteCollection();
        $routeCollection->add('simple_first_page_route', new Route(
            '/some_route/first_page/'
        ));

        return new UrlGenerator($routeCollection, new RequestContext(), new LoggerMock());
    }
}
