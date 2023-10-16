<?php

namespace Tests\Unit\Mock;

use Knp\Component\Pager\Pagination\AbstractPagination;

class KnpPaginationMock extends AbstractPagination
{
    public function __construct(int $page, int $totalCount, int $totalPageCount)
    {
        $this->setCurrentPageNumber($page);
        $this->setTotalItemCount($totalCount);
        $this->setItemNumberPerPage($totalCount / $totalPageCount);
    }
}
