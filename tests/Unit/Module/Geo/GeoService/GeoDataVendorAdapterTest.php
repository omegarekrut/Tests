<?php

namespace Tests\Unit\Module\Geo\GeoService;

use App\Module\Geo\Exceptions\InvalidLocationFromVendorException;
use App\Module\Geo\Service\GeoDataVendorAdapter;
use App\Module\Geo\TransferObject\LocationDTO;
use App\Module\Geo\TransferObject\LocationDTOFactory;
use Dadata\DadataClient as GeoDataVendorClient;
use Psr\SimpleCache\CacheInterface;
use Symfony\Component\Cache\Simple\ArrayCache;
use Tests\Unit\LoggerMock;
use Tests\Unit\TestCase;

class GeoDataVendorAdapterTest extends TestCase
{
    private LoggerMock $logger;
    private CacheInterface $cache;

    protected function setUp(): void
    {
        parent::setUp();

        $this->logger = new LoggerMock();
        $this->cache = new ArrayCache();
    }

    public function testGetLocationByIp(): void
    {
        $expectedLocationDTO = $this->createMock(LocationDTO::class);
        $locationDTOFactory = $this->createMock(LocationDTOFactory::class);
        $locationDTOFactory->method('createFromGeoDataLocation')->willReturn($expectedLocationDTO);

        $geoDataVendor = $this->createMock(GeoDataVendorClient::class);
        $expectedData = ['some info' => 'value'];
        $geoDataVendor->method('iplocate')->willReturn($expectedData);

        $service = new GeoDataVendorAdapter($geoDataVendor, $this->logger, $locationDTOFactory, $this->cache);

        $actualLocationDTO = $service->getLocationByIp('127.0.0.1');

        $this->assertEquals($expectedLocationDTO, $actualLocationDTO);

        $cacheData = $this->cache->getValues();

        $this->assertNotEmpty($cacheData);
        $this->assertEquals(serialize($expectedData), reset($cacheData));
    }

    public function testGetLocationDTOFactoryForInvalideResponseFromGeoDataVendorClient(): void
    {
        $expectedLocationDTO = $this->createMock(LocationDTO::class);
        $locationDTOFactory = $this->createMock(LocationDTOFactory::class);
        $locationDTOFactory->method('createDefault')->willReturn($expectedLocationDTO);

        $geoDataVendor = $this->createMock(GeoDataVendorClient::class);
        $geoDataVendor->method('iplocate')->willReturn('');

        $service = new GeoDataVendorAdapter($geoDataVendor, $this->logger, $locationDTOFactory, $this->cache);

        $actualLocationDTO = $service->getLocationByIp('127.0.0.1');

        $this->assertEquals($expectedLocationDTO, $actualLocationDTO);
        $this->assertNotEmpty($this->cache->getValues());
    }

    public function testGetLocationDTOFactoryForExceptionInLocationDtoFactory(): void
    {
        $errorMessage = 'test error message';
        $locationDTOFactory = $this->createMock(LocationDTOFactory::class);
        $locationDTOFactory
            ->method('createFromGeoDataLocation')
            ->willThrowException(new InvalidLocationFromVendorException($errorMessage));

        $geoDataVendor = $this->createMock(GeoDataVendorClient::class);
        $geoDataVendor->method('iplocate')->willReturn([]);

        $service = new GeoDataVendorAdapter($geoDataVendor, $this->logger, $locationDTOFactory, $this->cache);

        $this->assertNotNull($service->getLocationByIp('127.0.0.1'));

        $receivedLogMessages = $this->logger->getMessages();
        $expectedLogMessage = sprintf('Определение местоположения по ip не удалось. Ошибка: %s', $errorMessage);

        $this->assertEquals($expectedLogMessage, $receivedLogMessages[0]['message']);
        $this->assertEquals('warning', $receivedLogMessages[0]['level']);
        $this->assertNotEmpty($this->cache->getValues());
    }
}
