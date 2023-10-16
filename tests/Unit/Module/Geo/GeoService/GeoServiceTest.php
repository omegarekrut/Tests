<?php

namespace Tests\Unit\Module\Geo\GeoService;

use App\Module\Geo\Service\GeoDataVendorAdapterInterface;
use App\Module\Geo\Service\GeoService;
use App\Module\Geo\TransferObject\LocationDTO;
use Tests\Unit\TestCase;

class GeoServiceTest extends TestCase
{
    public function testGeoServiceShouldCreatedLocation(): void
    {
        $expectedLocation = new LocationDTO();

        $geoService = new GeoService($this->getGeoDataVendorAdapter($expectedLocation));

        $locationFromService = $geoService->getLocationByIpOrDefault('127.0.0.1');

        $this->assertEquals($expectedLocation, $locationFromService);
    }

    private function getGeoDataVendorAdapter(LocationDTO $location): GeoDataVendorAdapterInterface
    {
        $geoDataVendorAdapter = $this->createMock(GeoDataVendorAdapterInterface::class);
        $geoDataVendorAdapter->method('getLocationByIp')
            ->willReturn($location);

        return $geoDataVendorAdapter;
    }
}
