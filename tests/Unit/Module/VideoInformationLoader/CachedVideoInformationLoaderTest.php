<?php

namespace Tests\Unit\Module\VideoInformationLoader;

use App\Module\VideoInformationLoader\CachedVideoInformationLoader;
use App\Module\VideoInformationLoader\VideoInformation;
use App\Module\VideoInformationLoader\VideoInformationLoaderMock;
use Psr\SimpleCache\CacheInterface;
use Symfony\Component\Cache\Simple\ArrayCache;
use Tests\Unit\TestCase;

class CachedVideoInformationLoaderTest extends TestCase
{
    public function testLoaderWithoutCacheMustLoadInformationFromOriginalLoader(): void
    {
        $expectedVideoInformation = $this->createVideoInformation();
        $originalLoader = new VideoInformationLoaderMock($expectedVideoInformation);
        $emptyCache = $this->createCacheWithVideoInformation();

        $cachedVideoInformationLoader = new CachedVideoInformationLoader($originalLoader, $emptyCache);
        $videoInformation = $cachedVideoInformationLoader->loadInformation('http://foo.bar');

        $this->assertTrue($expectedVideoInformation === $videoInformation);
    }

    public function testVideoInformationLoadedFromOriginLoaderMustBeStoredInCache(): void
    {
        $cache = new ArrayCache();
        $originalLoader = new VideoInformationLoaderMock($this->createVideoInformation());

        $cachedVideoInformationLoader = new CachedVideoInformationLoader($originalLoader, $cache);
        $cachedVideoInformationLoader->loadInformation('http://foo.bar');

        $this->assertNotEmpty($cache->getValues());
    }

    public function testInformationMustBeLoadedFromCacheIfExist(): void
    {
        $informationFromCache = $this->createVideoInformation();
        $cache = $this->createCacheWithVideoInformation($informationFromCache);

        $originalLoader = new VideoInformationLoaderMock($this->createVideoInformation());

        $cachedVideoInformationLoader = new CachedVideoInformationLoader($originalLoader, $cache);
        $videoInformation = $cachedVideoInformationLoader->loadInformation('http://foo.bar');

        $this->assertTrue($informationFromCache === $videoInformation);
    }

    private function createCacheWithVideoInformation(?VideoInformation $videoInformation = null): CacheInterface
    {
        $stub = $this->createMock(CacheInterface::class);
        $stub
            ->method('has')
            ->willReturn((bool) $videoInformation);
        $stub
            ->method('get')
            ->willReturn($videoInformation);

        return $stub;
    }

    private function createVideoInformation(): VideoInformation
    {
        return new VideoInformation('http://foo.bar/video', 'some title', 'http://foo.bar/image', '<video/>');
    }
}
