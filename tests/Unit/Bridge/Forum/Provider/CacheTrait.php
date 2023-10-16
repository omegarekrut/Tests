<?php

namespace Tests\Unit\Bridge\Forum\Provider;

use Psr\SimpleCache\CacheInterface;
use Symfony\Component\Cache\Simple\ArrayCache;

trait CacheTrait
{
    private function createCache(): CacheInterface
    {
        return new ArrayCache();
    }
}
