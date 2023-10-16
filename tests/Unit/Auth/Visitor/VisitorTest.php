<?php

namespace Tests\Unit\Auth\Visitor;

use App\Auth\Visitor\Alert\AlertResolver;
use App\Auth\Visitor\CompanyAuthor\CompanyAuthorServiceInterface;
use App\Auth\Visitor\GaugingStationViewer\ViewedGaugingStationInCookieStorage;
use App\Auth\Visitor\Profile\ProfileFactoryInterface;
use App\Auth\Visitor\Service\LocationServiceInterface;
use App\Auth\Visitor\Visitor;
use App\Domain\Region\Entity\Region;
use App\Module\Geo\TransferObject\LocationDTO;
use App\Service\ClientIp;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Tests\Unit\TestCase;
use DateTimeZone;

class VisitorTest extends TestCase
{
    public function testGetTimeZoneWithMaterialregion(): void
    {
        $expectedDateTimeZone = new DateTimeZone('+1000');

        $region = $this->createMock(Region::class);
        $region->method('getDateTimeZone')->willReturn($expectedDateTimeZone);

        $locationService = $this->createMock(LocationServiceInterface::class);
        $locationService->method('isExistMaterialsRegion')->willReturn(true);
        $locationService->method('getMaterialsRegion')->willReturn($region);

        $visitor = new Visitor(
            $this->createMock(TokenStorageInterface::class),
            $locationService,
            $this->createMock(ProfileFactoryInterface::class),
            $this->createMock(ClientIp::class),
            $this->createMock(AlertResolver::class),
            $this->createMock(ViewedGaugingStationInCookieStorage::class),
            $this->createMock(CompanyAuthorServiceInterface::class),
        );

        $this->assertEquals($expectedDateTimeZone, $visitor->getTimeZone());
    }

    public function testGetTimeZoneWithEmptyMaterialregion(): void
    {
        $location = new LocationDTO();
        $location->region = null;

        $locationService = $this->createMock(LocationServiceInterface::class);
        $locationService->method('isExistMaterialsRegion')->willReturn(false);
        $locationService->method('getLocation')->willReturn($location);

        $visitor = new Visitor(
            $this->createMock(TokenStorageInterface::class),
            $locationService,
            $this->createMock(ProfileFactoryInterface::class),
            $this->createMock(ClientIp::class),
            $this->createMock(AlertResolver::class),
            $this->createMock(ViewedGaugingStationInCookieStorage::class),
            $this->createMock(CompanyAuthorServiceInterface::class),
        );

        $this->assertEquals(new DateTimeZone('Asia/Novosibirsk'), $visitor->getTimeZone());
    }
}
