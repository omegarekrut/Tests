<?php

namespace Tests\Functional\Module\Pagination\Mock;

use App\Module\PaginationRouting\Annotations\PaginationFactoryConfiguration;

class PaginationController
{
    public function actionWithoutAnnotation(): void
    {
    }

    /**
     * @PaginationFactoryConfiguration(
     *     firstPageRoute="some_route_index",
     *     paginationUsedRoute="some_route_pagination",
     * )
     */
    public function actionWithAnnotation(): void
    {
    }
}
