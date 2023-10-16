<?php

namespace Tests\Unit\Auth\Visitor\Service;

use App\Auth\Visitor\Service\Exceptions\LocationCookieException;
use App\Auth\Visitor\Service\LocationCookieService;
use App\Domain\Region\Entity\Region;
use App\Domain\Region\Repository\RegionRepository;
use App\Module\Geo\TransferObject\LocationDTO;
use App\Service\ClientIp;
use App\Util\Cookie\Cookie;
use App\Util\Cookie\CookieCollection;
use App\Util\Coordinates\Coordinates;
use Ramsey\Uuid\Uuid;
use Tests\Unit\Mock\CookieCollectionMock;

class LocationCookieServiceTest extends LocationServicesTestService
{
    private CookieCollection $cookieCollection;
    private ClientIp $clientIp;
    private LocationDTO $expectedLocation;

    protected function setUp(): void
    {
        parent::setUp();

        $this->cookieCollection = new CookieCollectionMock();
        $this->clientIp = $this->getClientIp();
        $this->expectedLocation = $this->createLocation();
    }

    public function testGetLocationCookie(): void
    {
        $cookie = new Cookie(LocationCookieService::COOKIE_NAME, 'some cookie value');
        $regionRepository = $this->createMock(RegionRepository::class);

        $this->cookieCollection->add($cookie);
        $service = new LocationCookieService($this->cookieCollection, $this->clientIp, $regionRepository);
        $this->assertEquals($cookie, $service->getLocationCookie());
    }

    public function testParseLocationCookie(): void
    {
        $regionRepository = $this->createMock(RegionRepository::class);
        $regionRepository->method('findById')->willReturn($this->expectedLocation->region);
        $service = new LocationCookieService($this->cookieCollection, $this->clientIp, $regionRepository);

        $cookieValue = $this->createExpectedCookieValueFromLocation($this->expectedLocation);
        $parsedData = $service->parseLocationCookie($cookieValue);

        $this->assertEquals($parsedData['ip'], $this->clientIp->getIp());
        $this->assertEquals($parsedData['region'], $this->expectedLocation->region);
        $this->assertEquals($parsedData['city'], $this->expectedLocation->city);
        $this->assertEquals($parsedData['geo_lat'], $this->expectedLocation->coordinates->getLatitude());
        $this->assertEquals($parsedData['geo_lon'], $this->expectedLocation->coordinates->getLongitude());
    }

    public function testCreateLocationByCookie(): void
    {
        $cookieValue = $this->createExpectedCookieValueFromLocation($this->expectedLocation);
        $regionRepository = $this->createMock(RegionRepository::class);

        $service = new LocationCookieService($this->cookieCollection, $this->clientIp, $regionRepository);
        $service->rememberInCookie($this->expectedLocation);
        $cookie = $this->cookieCollection->getLast();

        $this->assertInstanceOf(Cookie::class, $cookie);
        $this->assertEquals($cookieValue, $cookie->getValue());
    }

    /**
     * @dataProvider getInvalidLocationCookieValue
     */
    public function testFailedParseLocationCookieValue(string $locationCookieValue, string $exceptionMessage): void
    {
        $regionRepository = $this->createMock(RegionRepository::class);

        $service = new LocationCookieService($this->cookieCollection, $this->clientIp, $regionRepository);

        $this->expectException(LocationCookieException::class);
        $this->expectExceptionMessage($exceptionMessage);

        $service->parseLocationCookie($locationCookieValue);
    }

    private function createLocation(): LocationDTO
    {
        $regionMock = $this->createMock(Region::class);
        $regionMock->method('getId')->willReturn(Uuid::uuid4());

        $location = new LocationDTO();
        $location->city = 'Название города';
        $location->region = $regionMock;
        $location->coordinates = new Coordinates(
            54.5,
            55.4
        );

        return $location;
    }

    public function getInvalidLocationCookieValue(): \Generator
    {
        yield [
            '127.0.0/Новосибирск',
            'Количество элементов массива значений кук не совпадает с ожидаемым количеством.',
        ];

        yield [
            '127.0.0/61207a32-0f7c-4fdb-ad20-b91bbac6cdf8/Новосибирск/54.2',
            'Количество элементов массива координат не совпадает с ожидаемым количеством.',
        ];
    }
}
