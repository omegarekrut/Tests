<?php

namespace Tests\Unit\Module\Seo\Extension\Pagination;

use App\Module\Seo\Extension\Pagination\Exception\UnsupportedPaginatorFormatException;
use App\Module\Seo\Extension\Pagination\Factory\KnpPaginationFactory;
use App\Module\Seo\TransferObject\PagePagination;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Tests\Unit\LoggerMock;
use Tests\Unit\Mock\KnpPaginationMock;
use Tests\Unit\TestCase;

/**
 * @group seo
 */
class KnpPaginationFactoryTest extends TestCase
{
    private $requestStack;

    protected function setUp(): void
    {
        parent::setUp();

        $this->requestStack = new RequestStack();
    }

    /**
     * @dataProvider getPaginationFixture
     */
    public function testCreation(array $attributes, array $paginationParameters, PagePagination $expectedPagePagination, array $query = []): void
    {
        $this->requestStack->push(new Request($query, [], $attributes));

        $factory = new KnpPaginationFactory($this->requestStack, $this->getUrlGenerator());
        $paginator = $this->createPaginator($paginationParameters);

        $this->assertEquals($expectedPagePagination, $factory->createPaginationByPaginator($paginator));
    }

    public function getPaginationFixture(): \Generator
    {
        yield [
            [
                '_route' => 'some_route_pagination',
                '_route_params' => ['page' => 3],
            ],
            [
                'totalCount' => 25,
                'totalPageCount' => 5,
                'page' => 3,
            ],
            new PagePagination(3, 5, '/some_route/page2/', '/some_route/page4/'),
        ];

        yield [
            [
                '_route' => 'some_route_pagination',
                '_route_params' => ['page' => 1],
            ],
            [
                'totalCount' => 1,
                'totalPageCount' => 1,
                'page' => 1,
            ],
            new PagePagination(1, 1),
        ];

        yield [
            [
                '_route' => 'some_route_pagination',
                '_route_params' => ['page' => 5],
            ],
            [
                'totalCount' => 25,
                'totalPageCount' => 5,
                'page' => 5,
            ],
            new PagePagination(5, 5, '/some_route/page4/'),
        ];

        yield [
            [
                '_route' => 'some_route_pagination',
                '_route_params' => ['page' => 1],
            ],
            [
                'totalCount' => 25,
                'totalPageCount' => 5,
                'page' => 1,
            ],
            new PagePagination(1, 5, null, '/some_route/page2/'),
        ];

        yield [
            [
                '_route' => 'some_route_pagination',
                '_route_params' => ['page' => 2],
            ],
            [
                'totalCount' => 25,
                'totalPageCount' => 5,
                'page' => 2,
            ],
            new PagePagination(2, 5, '/some_route/page1/', '/some_route/page3/'),
        ];
    }

    public function testUnsupportedPaginator(): void
    {
        $this->expectException(UnsupportedPaginatorFormatException::class);

        $this->requestStack->push(new Request());
        $factory = new KnpPaginationFactory($this->requestStack, $this->getUrlGenerator());
        $factory->createPaginationByPaginator(new \stdClass());
    }

    private function createPaginator(array $parameters): KnpPaginationMock
    {
        return new KnpPaginationMock($parameters['page'], $parameters['totalCount'], $parameters['totalPageCount']);
    }

    private function getUrlGenerator(): UrlGenerator
    {
        $routeCollection = new RouteCollection();
        $routeCollection->add('some_route', new Route(
            '/some_route/'
        ));
        $routeCollection->add('some_route_pagination', new Route(
            '/some_route/page{page}/',
            ['page' => 1],
            ['page' => '\d+']
        ));

        return new UrlGenerator($routeCollection, new RequestContext(), new LoggerMock());
    }
}
