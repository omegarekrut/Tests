<?php

namespace Tests\Unit\Module\Seo\Extension\Pagination;

use App\Module\Seo\Extension\Pagination\Exception\UnsupportedPaginatorFormatException;
use App\Module\Seo\Extension\Pagination\Factory\PaginationFactoryInterface;
use App\Module\Seo\Extension\Pagination\PaginationExtension;
use App\Module\Seo\Extension\SeoContext;
use App\Module\Seo\TransferObject\PagePagination;
use App\Module\Seo\TransferObject\SeoPage;
use PHPUnit\Framework\MockObject\Stub\Exception;
use Tests\Unit\TestCase;

/**
 * @group seo
 */
class PaginationExtensionTest extends TestCase
{
    /** @var iterable */
    private $context;

    /** @var SeoPage */
    private $seoPage;

    protected function setUp(): void
    {
        parent::setUp();

        $this->context = new SeoContext([
            $this,
            new \stdClass(),
        ]);

        $this->seoPage = new SeoPage();
    }

    public function testApply(): void
    {
        $pagePagination = $this->createMock(PagePagination::class);
        $paginationExtension = new PaginationExtension($this->createPaginationFactory($this->context, $pagePagination));
        $paginationExtension->apply($this->seoPage, $this->context);

        $this->assertEquals($pagePagination, $this->seoPage->getPagination());
    }

    public function testUnsupportedContext(): void
    {
        $paginationExtension = new PaginationExtension($this->createPaginationFactory($this->context, null));
        $paginationExtension->apply($this->seoPage, $this->context);

        $this->assertEquals(1, $this->seoPage->getPagination()->getPageNumber());
        $this->assertEquals(1, $this->seoPage->getPagination()->getTotalPages());
        $this->assertFalse($this->seoPage->getPagination()->hasNextPage());
        $this->assertFalse($this->seoPage->getPagination()->hasPreviousPage());
    }

    private function createPaginationFactory(iterable $context, ?PagePagination $pagePagination = null): PaginationFactoryInterface
    {
        $stub = $this->createMock(PaginationFactoryInterface::class);

        $returnMap = [];

        foreach ($context as $value) {
            $returnMap[] = new Exception(new UnsupportedPaginatorFormatException());
        }

        if ($pagePagination) {
            $lastKey = array_key_last($returnMap);

            $returnMap[$lastKey] = $pagePagination;
        }

        $stub
            ->method('createPaginationByPaginator')
            ->willReturnOnConsecutiveCalls(...$returnMap);

        return $stub;
    }
}
