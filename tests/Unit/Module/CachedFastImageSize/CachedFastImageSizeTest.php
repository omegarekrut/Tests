<?php

namespace Tests\Unit\Module\CachedFastImageSize;

use App\Module\CachedFastImageSize\CachedFastImageSize;
use Psr\SimpleCache\CacheInterface;
use Tests\Unit\TestCase;

class CachedFastImageSizeTest extends TestCase
{
    private const CACHED_SIZE = ['size' => 'in cache'];

    public function testLoadFromCache(): void
    {
        $cache = $this->createCache(true, self::CACHED_SIZE);
        $imageSize = new CachedFastImageSize($cache);

        $this->assertEquals(self::CACHED_SIZE, $imageSize->getImageSize('for.bar'));
    }

    public function testSaveToCache(): void
    {
        $cache = $this->createCache(false);
        $imageSize = new CachedFastImageSize($cache);

        $imageSize->getImageSize($this->getDataFixturesFolder().'image20x29.jpeg');
    }

    /**
     * @param mixed[]|null $sizeInCache
     */
    private function createCache(bool $inCache, ?array $sizeInCache = null): CacheInterface
    {
        $stub = $this->createMock(CacheInterface::class);
        $stub
            ->method('has')
            ->willReturn($inCache);
        $stub
            ->expects($inCache ? $this->once() : $this->never())
            ->method('get')
            ->willReturn($sizeInCache);
        $stub
            ->expects($inCache ? $this->never() : $this->once())
            ->method('set');

        return $stub;
    }
}
