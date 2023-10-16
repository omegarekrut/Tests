<?php

namespace Tests\Unit\Auth\Visitor\Service;

use App\Auth\Visitor\MaterialsRegion\MaterialsRegionInCookieStorage;
use App\Auth\Visitor\Service\LocationCookieService;
use App\Auth\Visitor\Service\LocationService;
use App\Domain\Region\Entity\Region;
use App\Domain\Region\Repository\RegionRepository;
use App\Module\Geo\Service\GeoService;
use App\Module\Geo\TransferObject\LocationDTO;
use App\Module\Geo\TransferObject\LocationDTOFactory;
use App\Service\ClientIp;
use App\Service\SearchBotDetector;
use App\Util\Cookie\Cookie;
use App\Util\Cookie\CookieCollection;
use App\Util\Cookie\CookieInterface;
use App\Util\Coordinates\Coordinates;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Tests\Unit\LoggerMock;
use Tests\Unit\Mock\CookieCollectionMock;

class LocationServiceTest extends LocationServicesTestService
{
    private CookieCollection $cookieCollection;
    private ClientIp $clientIp;
    private LocationDTO $defaultLocation;
    private LocationDTO $expectedLocation;
    private GeoService $geoService;
    private LoggerMock $logger;

    protected function setUp(): void
    {
        parent::setUp();

        $this->cookieCollection = new CookieCollectionMock();
        $this->clientIp = $this->getClientIp();
        $this->defaultLocation = $this->createDefaultLocation();
        $this->expectedLocation = $this->createLocation();
        $this->geoService = $this->createGeoServiceMock($this->expectedLocation);
        $this->logger = new LoggerMock();
    }

    public function testRememberNewLocationInCookie(): void
    {
        $this->cookieCollection->add(new Cookie(LocationCookieService::COOKIE_NAME, '127.0.0.2/nameOld//0,0'));

        $service = $this->createLocationService();
        $location = $service->getLocation();
        $cookie = $this->cookieCollection->getLast();

        $expectedCookieValue = $this->createExpectedCookieValueFromLocation($location);

        $this->assertEquals($this->expectedLocation, $location);
        $this->assertInstanceOf(Cookie::class, $cookie);
        $this->assertNotEquals($expectedCookieValue, $cookie->getValue());
    }

    public function testCreateLocationByCookie(): void
    {
        $cookieValue = $this->createExpectedCookieValueFromLocation($this->expectedLocation);
        $this->cookieCollection->add(new Cookie(LocationCookieService::COOKIE_NAME, $cookieValue));

        $service = $this->createLocationService();
        $location = $service->getLocation();
        $cookie = $this->cookieCollection->getLast();

        $this->assertEquals($this->expectedLocation, $location);
        $this->assertInstanceOf(Cookie::class, $cookie);
        $this->assertEquals($cookieValue, $cookie->getValue());
    }

    public function testGetLocationWithCookie(): void
    {
        $service = $this->createLocationService();
        $location = $service->getLocation();

        $this->assertEquals($this->expectedLocation, $location);
    }

    public function testGetLocationWithoutCookie(): void
    {
        $service = $this->createLocationService();
        $location = $service->getLocation();

        $this->assertEquals($this->expectedLocation, $location);
    }

    public function testGetMaterialsRegionWithCookie(): void
    {
        $materialsRegionCookie = $this->createMock(Cookie::class);
        $materialsRegionCookie->method('getValue')->willReturn('some value');

        $materialsRegion = $this->createRegionMock(Uuid::uuid4());

        $service = $this->createLocationService($materialsRegionCookie, $materialsRegion);

        $expectedMaterialsRegion = $service->getMaterialsRegion();

        $this->assertEquals($expectedMaterialsRegion, $materialsRegion);
    }

    public function testGetMaterialsRegionWithoutCookie(): void
    {
        $service = $this->createLocationService();
        $materialsRegion = $service->getMaterialsRegion();

        $this->assertNull($materialsRegion);
    }

    public function testGetLocationForBot(): void
    {
        $service = $this->createLocationService(null, null, true);
        $location = $service->getLocation();

        $this->assertEquals($this->defaultLocation, $location);
    }

    private function createLocationService(?CookieInterface $cookie = null, ?Region $region = null, bool $isBot = false): LocationService
    {
        $materialsRegionInCookieStorage = $this->createMaterialsRegionInCookieStorageMock($cookie, $region);

        $searchBotDetector = $this->createSearchBotDetector($isBot);

        $regionRepositoryMock = $this->createMock(RegionRepository::class);
        $regionRepositoryMock
            ->method('findById')
            ->willReturn($this->createRegionMock(Uuid::uuid4()));

        $cookieService = new LocationCookieService(
            $this->cookieCollection,
            $this->createMock(ClientIp::class),
            $regionRepositoryMock,
        );

        $locationDTOFactory = $this->createMock(LocationDTOFactory::class);
        $locationDTOFactory->method('createDefault')->willReturn($this->defaultLocation);
        $locationDTOFactory->method('createFromLocationInfo')->willReturn($this->createLocation());
        $locationDTOFactory->method('createFromGeoDataLocation')->willReturn($this->createLocation());

        return new LocationService($this->geoService, $locationDTOFactory, $searchBotDetector, $this->clientIp, $cookieService, $materialsRegionInCookieStorage, $this->logger);
    }

    private function createMaterialsRegionInCookieStorageMock(?CookieInterface $cookie, ?Region $region): MaterialsRegionInCookieStorage
    {
        $materialsRegionInCookieStorage = $this->createMock(MaterialsRegionInCookieStorage::class);
        $materialsRegionInCookieStorage->method('getCookie')->willReturn($cookie);
        $materialsRegionInCookieStorage->method('parseCookie')->willReturn($region);

        return $materialsRegionInCookieStorage;
    }

    private function createGeoServiceMock(LocationDTO $locationDTO): GeoService
    {
        $mock = $this->createMock(GeoService::class);

        $mock->method('getLocationByIpOrDefault')
            ->willReturn($locationDTO);

        return $mock;
    }

    private function createLocation(): LocationDTO
    {
        $regionMock = $this->createRegionMock(Uuid::uuid4());

        $location = new LocationDTO();
        $location->city = 'Название города';
        $location->region = $regionMock;
        $location->coordinates = new Coordinates(
            54.5,
            55.4
        );

        return $location;
    }

    private function createDefaultLocation(): LocationDTO
    {
        $regionMock = $this->createRegionMock(Uuid::uuid4());

        $location = new LocationDTO();
        $location->city = 'Город по умолчанию';
        $location->region = $regionMock;
        $location->coordinates = new Coordinates(
            84.5,
            95.4
        );

        return $location;
    }

    private function createRegionMock(UuidInterface $id): Region
    {
        $regionMock = $this->createMock(Region::class);
        $regionMock->method('getId')->willReturn($id);

        return $regionMock;
    }
    private function createSearchBotDetector(bool $isBot): SearchBotDetector
    {
        $searchBotDetector = $this->createMock(SearchBotDetector::class);
        $searchBotDetector->method('isBot')->willReturn($isBot);

        return $searchBotDetector;
    }
}
