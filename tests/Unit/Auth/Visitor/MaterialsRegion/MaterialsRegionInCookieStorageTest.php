<?php

namespace Tests\Unit\Auth\Visitor\MaterialsRegion;

use App\Auth\Visitor\MaterialsRegion\MaterialsRegionInCookieStorage;
use App\Domain\Region\Entity\Region;
use App\Domain\Region\Repository\RegionRepository;
use App\Util\Cookie\Cookie;
use App\Util\Cookie\CookieCollection;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Tests\Unit\TestCase;

class MaterialsRegionInCookieStorageTest extends TestCase
{
    private CookieCollection $cookieCollection;
    private RegionRepository $regionRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->cookieCollection = $this->createMock(CookieCollection::class);
        $this->regionRepository = $this->createMock(RegionRepository::class);
    }

    public function testGetCookieWithoutCookie(): void
    {
        $materialsRegionInCookieStorage = new MaterialsRegionInCookieStorage($this->cookieCollection, $this->regionRepository);

        $cookie = $materialsRegionInCookieStorage->getCookie();

        $this->assertNull($cookie);
    }

    public function testGetCookieWithCookie(): void
    {
        $cookie = $this->createMock(Cookie::class);
        $cookieCollection = $this->createMock(CookieCollection::class);
        $cookieCollection->method('get')->willReturn($cookie);

        $materialsRegionInCookieStorage = new MaterialsRegionInCookieStorage($cookieCollection, $this->regionRepository);

        $expectedCookie = $materialsRegionInCookieStorage->getCookie();

        $this->assertSame($expectedCookie, $cookie);
    }

    public function testParseCookieWithExistingRegion(): void
    {
        $regionMock = $this->createRegionMock(Uuid::uuid4());
        $regionRepositoryMock = $this->createMock(RegionRepository::class);
        $regionRepositoryMock->method('findById')->willReturn($regionMock);

        $materialsRegionInCookieStorage = new MaterialsRegionInCookieStorage($this->cookieCollection, $regionRepositoryMock);

        $expectedRegion = $materialsRegionInCookieStorage->parseCookie('some value');

        $this->assertSame($expectedRegion, $regionMock);
    }

    public function testParseCookieWithoutExistingRegion(): void
    {
        $regionRepositoryMock = $this->createMock(RegionRepository::class);
        $regionRepositoryMock->method('findById')->willReturn(null);

        $materialsRegionInCookieStorage = new MaterialsRegionInCookieStorage($this->cookieCollection, $regionRepositoryMock);

        $expectedRegion = $materialsRegionInCookieStorage->parseCookie('some value');

        $this->assertNull($expectedRegion);
    }

    private function createRegionMock(UuidInterface $id): Region
    {
        $regionMock = $this->createMock(Region::class);
        $regionMock->method('getId')->willReturn($id);

        return $regionMock;
    }
}
