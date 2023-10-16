<?php

namespace Tests\Unit\Module\OAuth\Factory;

use App\Module\OAuth\PropertyAccessor\DecoratingPropertyAccessor;
use Symfony\Component\PropertyAccess\PropertyAccess;

trait DecoratedPropertyAccessorTrait
{
    private function createDecoratedPropertyAccessor(): DecoratingPropertyAccessor
    {
        return new DecoratingPropertyAccessor(PropertyAccess::createPropertyAccessor());
    }
}
