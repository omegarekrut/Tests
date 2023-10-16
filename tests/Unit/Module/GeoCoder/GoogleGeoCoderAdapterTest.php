<?php

namespace Tests\Unit\Module\GeoCoder;

use App\Module\GeoCoder\GoogleGeoCoderAdapter;
use App\Module\GeoCoder\GoogleGeoCoderClient;
use App\Util\Coordinates\Coordinates;
use Tests\Unit\LoggerMock as Logger;
use Tests\Unit\TestCase;

class GoogleGeoCoderAdapterTest extends TestCase
{
    private Logger $logger;
    private Coordinates $coordinates;

    protected function setUp(): void
    {
        parent::setUp();

        $this->logger = new Logger();

        $this->coordinates = new Coordinates(
            55.0520939,
            82.874782
        );
    }

    protected function tearDown(): void
    {
        unset(
            $this->logger,
            $this->coordinates,
        );

        parent::tearDown();
    }

    public function testGeoCoderAdapterShouldReturnRegionNameByCoordinates(): void
    {
        $location = $this->getValidLocation();
        $googleGeoCoderAdapter = $this->getGoogleGeoCoderAdapterInstance($location);

        $regionName = $googleGeoCoderAdapter->getRegionNameByCoordinates($this->coordinates);

        $this->assertEquals('Новосибирская область', $regionName);
    }

    /**
     * @param string[] $location
     *
     * @dataProvider invalidLocationDataProvider
     */
    public function testFailGetRegionByCoordinates(array $location, string $logMessage): void
    {
        $googleGeoCoderAdapter = $this->getGoogleGeoCoderAdapterInstance($location);
        $this->assertNull($googleGeoCoderAdapter->getRegionNameByCoordinates($this->coordinates));

        $receivedLogMessages = $this->logger->getMessages();

        $this->assertEquals($logMessage, $receivedLogMessages[0]['message']);
        $this->assertEquals('warning', $receivedLogMessages[0]['level']);
    }

    /**
     * @return string[]
     */
    public function invalidLocationDataProvider(): array
    {
        return [
            'Invalid full address. Missing region.' => [
                $this->getInvalidLocation(),
                'Ошибка получение фрагмента адреса из местоположения. По заданной метке administrative_area_level_1 фрагмент адресса не был найден',
            ],
        ];
    }

    /**
     * @param string[] $location
     *
     * @return GoogleGeoCoderAdapter
     */
    private function getGoogleGeoCoderAdapterInstance(array $location): GoogleGeoCoderAdapter
    {
        $googleGeoCoderClient = $this->createMock(GoogleGeoCoderClient::class);
        $googleGeoCoderClient->method('locateByCoordinates')
            ->willReturn($location);

        return new GoogleGeoCoderAdapter($this->logger, $googleGeoCoderClient);
    }

    /**
     * @return string[]
     */
    private function getInvalidLocation(): array
    {
        return [
            'results' => [
                0 => [
                    'address_components' => [
                        4 => [
                            'long_name' => 'Россия',
                            'types' => [
                                0 => 'country',
                                1 => 'political',
                            ],
                        ],
                    ],
                ],
            ],
            'status' => 'OK',
        ];
    }

    /**
     * @return string[]
     */
    private function getValidLocation(): array
    {
        return [
            'plus_code' =>  [
                'compound_code' => '3V2F+RWM Новосибирск, Новосибирская область, Россия',
                'global_code' => '9M743V2F+RWM',
            ],
            'results' => [
                0 => [
                    'address_components' => [
                        4 => [
                            'long_name' => 'Новосибирская область',
                            'short_name' => 'Новосибирская обл.',
                            'types' => [
                                0 => 'administrative_area_level_1',
                                1 => 'political',
                            ],
                        ],
                    ],
                    'formatted_address' => 'ул. Стасова, 4, Новосибирск, Новосибирская обл., Россия, 630001',
                    'geometry' => [
                        'location' => [
                            'lat' => 55.051969,
                            'lng' => 82.8747948,
                        ],
                        'location_type' => 'ROOFTOP',
                        'viewport' => [
                            'northeast' => [
                                'lat' => 55.053317980292,
                                'lng' => 82.876143780291,
                            ],
                            'southwest' => [
                                'lat' => 55.050620019709,
                                'lng' => 82.873445819708,
                            ],
                        ],
                    ],
                ],
            ],
            'status' => 'OK',
        ];
    }
}
