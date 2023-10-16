<?php

namespace Tests\Unit\Mock;

use App\Doctrine\NoMagic\IgnoreDoctrineFiltersAndSoftDeletableEnvironment;

/**
 * @deprecated
 */
class IgnoreDoctrineFiltersAndSoftDeletableEnvironmentMock extends IgnoreDoctrineFiltersAndSoftDeletableEnvironment
{
    public function __construct()
    {
    }

    public function __invoke(callable $call)
    {
        return call_user_func($call);
    }
}
