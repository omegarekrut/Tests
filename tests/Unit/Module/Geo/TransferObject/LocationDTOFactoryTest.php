<?php

namespace Tests\Unit\Module\Geo\TransferObject;

use App\Domain\Region\Entity\Region;
use App\Domain\Region\Repository\RegionRepository;
use App\Module\Geo\Exceptions\InvalidLocationFromVendorException;
use App\Module\Geo\TransferObject\LocationDTOFactory;
use Tests\Unit\LoggerMock;
use Tests\Unit\TestCase;

class LocationDTOFactoryTest extends TestCase
{
    /**
     * @dataProvider getInvalidDataForGeoDataLocationTransform
     *
     * @param mixed[] $geoDataLocation
     */
    public function testFailCreateFromGeoDataLocation(array $geoDataLocation, string $expectedErrorMessage): void
    {
        $this->expectException(InvalidLocationFromVendorException::class);
        $this->expectExceptionMessage($expectedErrorMessage);

        $regionRepository = $this->createMock(RegionRepository::class);

        $locationDTOFactory = new LocationDTOFactory($regionRepository, new LoggerMock());
        $locationDTOFactory->createFromGeoDataLocation($geoDataLocation);
    }

    public function getInvalidDataForGeoDataLocationTransform(): \Generator
    {
        yield 'withoutDataPlace' => [
            [],
            'Неверный формат ответа. В ответе нет данных по местоположению',
        ];

        yield 'withoutDataFederalIdentifier' => [
            [
                'data' => [],
            ],
            'Неверный формат ответа. В ответе нет данных по федеральному идентификатору',
        ];
    }

    /**
     * @dataProvider invalidLocationDataProvider
     *
     * @param mixed[] $locationInfo
     */
    public function testFailCreateFromLocationInfo(array $locationInfo, string $expectedMessage): void
    {
        $this->expectException(InvalidLocationFromVendorException::class);
        $this->expectExceptionMessage($expectedMessage);

        $regionRepository = $this->createMock(RegionRepository::class);

        $locationDTOFactory = new LocationDTOFactory($regionRepository, new LoggerMock());
        $locationDTOFactory->createFromLocationInfo($locationInfo);
    }

    public function testLoggedIfRegionNameIsUnknown(): void
    {
        $locationInfo = [
            'region' => 'Самарская',
            'city' => 'Самара',
            'geo_lat' => 53.0520939,
            'geo_lon' => 89.874782,
        ];

        $regionRepository = $this->createMock(RegionRepository::class);
        $logger = new LoggerMock();

        $locationDTOFactory = new LocationDTOFactory($regionRepository, $logger);
        $locationDTOFactory->createFromLocationInfo($locationInfo);

        $receivedLogMessages = $logger->getMessages();
        $expectedLogMessage = 'Не найден регион с коротким названием "Самарская"';

        $this->assertEquals($expectedLogMessage, $receivedLogMessages[0]['message']);
        $this->assertEquals('warning', $receivedLogMessages[0]['level']);
    }

    public function testCreateFromGeoDataLocation(): void
    {
        $locationInfo = [
            'data' => [
                'region' => 'Самарская',
                'city' => 'Самара',
                'geo_lat' => 53.0520939,
                'geo_lon' => 89.874782,
                'region_fias_id' => 'some-fias-id',
            ],
        ];

        $regionRepository = $this->getRegionRepository($locationInfo['data']['region']);

        $locationDTOFactory = new LocationDTOFactory($regionRepository, new LoggerMock());
        $locationDTO = $locationDTOFactory->createFromGeoDataLocation($locationInfo);

        $this->assertEquals($locationDTO->region->getName(), $locationInfo['data']['region']);
        $this->assertEquals($locationDTO->city, $locationInfo['data']['city']);
        $this->assertEquals($locationDTO->coordinates->getLatitude(), $locationInfo['data']['geo_lat']);
        $this->assertEquals($locationDTO->coordinates->getLongitude(), $locationInfo['data']['geo_lon']);
    }

    public function testCreateFromLocationInfo(): void
    {
        $locationInfo = [
            'region' => 'Самарская',
            'city' => 'Самара',
            'geo_lat' => 53.0520939,
            'geo_lon' => 89.874782,
        ];

        $regionRepository = $this->getRegionRepository($locationInfo['region']);

        $locationDTOFactory = new LocationDTOFactory($regionRepository, new LoggerMock());
        $locationDTO = $locationDTOFactory->createFromLocationInfo($locationInfo);

        $this->assertEquals($locationDTO->region->getName(), $locationInfo['region']);
        $this->assertEquals($locationDTO->city, $locationInfo['city']);
        $this->assertEquals($locationDTO->coordinates->getLatitude(), $locationInfo['geo_lat']);
        $this->assertEquals($locationDTO->coordinates->getLongitude(), $locationInfo['geo_lon']);
    }

    /**
     * @param array[] $locationInfo
     */
    public function testCreateFromCookieDataLocation(): void
    {
        $locationInfo = [
            'ip' => '127.0.0.1',
            'region' => 'some-region-id',
            'city' => 'Самара',
            'geo_lat' => 53.0520939,
            'geo_lon' => 89.874782,
        ];

        $regionRepository = $this->getRegionRepository($locationInfo['region']);

        $locationDTOFactory = new LocationDTOFactory($regionRepository, new LoggerMock());
        $locationDTO = $locationDTOFactory->createFromLocationInfo($locationInfo);

        $this->assertEquals($locationDTO->region->getName(), $locationInfo['region']);
        $this->assertEquals($locationDTO->city, $locationInfo['city']);
        $this->assertEquals($locationDTO->coordinates->getLatitude(), $locationInfo['geo_lat']);
        $this->assertEquals($locationDTO->coordinates->getLongitude(), $locationInfo['geo_lon']);
    }

    /**
     * @param array[] $locationInfo
     */
    public function testCreateDefaultLocation(): void
    {
        $regionRepository = $this->getRegionRepository(LocationDTOFactory::DEFAULT_REGION_NAME);

        $locationDTOFactory = new LocationDTOFactory($regionRepository, new LoggerMock());
        $locationDTO = $locationDTOFactory->createDefault();

        $this->assertEquals($locationDTO->region->getName(), LocationDTOFactory::DEFAULT_REGION_NAME);
        $this->assertEquals($locationDTO->city, LocationDTOFactory::DEFAULT_CITY);
        $this->assertEquals($locationDTO->coordinates->getLatitude(), LocationDTOFactory::DEFAULT_COORDINATES_LATITUDE);
        $this->assertEquals($locationDTO->coordinates->getLongitude(), LocationDTOFactory::DEFAULT_COORDINATES_LONGITUDE);
    }

    public function invalidLocationDataProvider(): \Generator
    {
        yield [
            [
                'reg2ion' => 'Самарская',
                'city' => 'Самара',
                'geo_lat' => 53.0520939,
                'geo_lon' => 89.874782,
            ],
            'Неверный формат ответа. В ответе нет данных по региону',
        ];

        yield [
            [
                'region' => 'Самарская',
                'cit2y' => 'Самара',
                'geo_lat' => 53.0520939,
                'geo_lon' => 89.874782,
            ],
            'Неверный формат ответа. В ответе нет данных по городу',
        ];

        yield [
            [
                'region' => 'Самарская',
                'city' => null,
                'geo_lat' => 53.0520939,
                'geo_lon' => 89.874782,
            ],
            'Неверный формат ответа. В ответе нет данных по городу',
        ];

        yield [
            [
                'region' => 'Самарская',
                'city' => 'Самара',
                'geo_l2at' => 53.0520939,
                'geo_lon' => 89.874782,
            ],
            'Неверный формат ответа. В ответе нет данных по широте',
        ];

        yield [
            [
                'region' => 'Самарская',
                'city' => 'Самара',
                'geo_lat' => 53.0520939,
                'geo_lo2n' => 89.874782,
            ],
            'Неверный формат ответа. В ответе нет данных по долготе',
        ];
    }

    private function getRegionRepository(string $regionName): RegionRepository
    {
        $regionRepository = $this->createMock(RegionRepository::class);

        $region = $this->createMock(Region::class);
        $region->method('getName')->willReturn($regionName);

        $regionRepository->method('findOneByMappingId')->willReturn($region);
        $regionRepository->method('findOneByShortName')->willReturn($region);
        $regionRepository->method('findOneByName')->willReturn($region);

        return $regionRepository;
    }
}
