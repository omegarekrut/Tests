<?php

namespace Tests\Unit\Module\PaginationRouting;

use App\Module\PaginationRouting\KnpPaginationFactory;
use Knp\Bundle\PaginatorBundle\Pagination\SlidingPagination;
use Knp\Component\Pager\Paginator;
use Tests\Unit\TestCase;

class PaginationFactoryTest extends TestCase
{
    private $paginationFactory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->paginationFactory = new KnpPaginationFactory($this->getPaginator());
    }

    public function testCreatePaginator(): void
    {
        $paginator = $this->paginationFactory->createPaginationForSource(null, 10, 2, true);

        $this->assertEquals('', $paginator->getRoute());
        $this->assertArrayNotHasKey('firstPageRoute', $paginator->getCustomParameters() ?: []);
    }

    public function testCreatePaginatorDefinedAttributes(): void
    {
        $factory = $this->paginationFactory->withPageRoutes('expected_first_page_route', 'expected_pagination_used_route');
        $paginator = $factory->createPaginationForSource(null, 10, 2, true);

        $this->assertEquals('expected_pagination_used_route', $paginator->getRoute());
        $this->assertArrayHasKey('firstPageRoute', $paginator->getCustomParameters());
        $this->assertContains('expected_first_page_route', $paginator->getCustomParameters());
    }

    private function getPaginator(): Paginator
    {
        $stub = $this->createMock(Paginator::class);
        $stub->method('paginate')
            ->willReturn(new SlidingPagination([]));

        return $stub;
    }
}
