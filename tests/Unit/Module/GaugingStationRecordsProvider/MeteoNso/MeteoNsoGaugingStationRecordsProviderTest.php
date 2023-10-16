<?php

namespace Tests\Unit\Module\GaugingStationRecordsProvider\MeteoNso;

use App\Domain\WaterLevel\Entity\ValueObject\GaugingStationRecordsProviderKey;
use App\Module\GaugingStationRecordsProvider\MeteoNso\GaugingStationDenormalizer;
use App\Module\GaugingStationRecordsProvider\MeteoNso\GaugingStationRecordNameAnalyzer;
use App\Module\GaugingStationRecordsProvider\MeteoNso\GaugingStationRecordsDenormalizer;
use App\Module\GaugingStationRecordsProvider\MeteoNso\MeteoNsoClient;
use App\Module\GaugingStationRecordsProvider\MeteoNso\MeteoNsoGaugingStationRecordsProvider;
use App\Module\GaugingStationRecordsProvider\MeteoNso\WaterDenormalizer;
use App\Module\GaugingStationRecordsProvider\TransferObject\GaugingStationRecord;
use App\Module\GaugingStationRecordsProvider\TransferObject\WaterType;
use DateTime;
use DateTimeZone;
use Psr\Log\LoggerInterface;
use Tests\Unit\TestCase;

/**
 * @group water-level
 * @group gauging-station-records-provider
 */
class MeteoNsoGaugingStationRecordsProviderTest extends TestCase
{
    public function testGetProviderName(): void
    {
        $gaugingStationRecordsProvider = new MeteoNsoGaugingStationRecordsProvider(
            $this->createMock(MeteoNsoClient::class),
            $this->createMock(GaugingStationRecordsDenormalizer::class),
            $this->createMock(LoggerInterface::class),
        );

        $this->assertEquals(GaugingStationRecordsProviderKey::meteoNso(), $gaugingStationRecordsProvider->getProviderKey());
    }

    /**
     * @param mixed[] $weather
     * @param mixed[] $waterTemperatures
     * @param mixed[] $expectedValues
     *
     * @dataProvider gaugingStations
     */
    public function testProvide(array $weather, array $waterTemperatures, array $expectedValues): void
    {
        $gaugingStationRecordsProvider = new MeteoNsoGaugingStationRecordsProvider(
            $this->createMeteoNsoClientMock($weather, $waterTemperatures),
            $this->createGaugingStationRecordsDenormalizer(),
        );

        $gaugingStationRecord = $gaugingStationRecordsProvider->provide()->current();
        assert($gaugingStationRecord instanceof GaugingStationRecord);

        $gaugingStationCoordinates = $gaugingStationRecord->getGaugingStationProvider()->getGeographicalPosition()->getCoordinates();

        $this->assertEquals($expectedValues['stationCoordinates']['latitude'], $gaugingStationCoordinates->getLatitude());
        $this->assertEquals($expectedValues['stationCoordinates']['longitude'], $gaugingStationCoordinates->getLongitude());
        $this->assertEquals($expectedValues['stationName'], $gaugingStationRecord->getGaugingStationProvider()->getName());
        $this->assertEquals($expectedValues['stationId'], $gaugingStationRecord->getGaugingStationProvider()->getId());
        $this->assertEquals($expectedValues['temperature'], $gaugingStationRecord->getTemperature());
        $this->assertEquals($expectedValues['waterName'], $gaugingStationRecord->getWater()->getName());
        $this->assertEquals($expectedValues['waterType'], $gaugingStationRecord->getWater()->getType());
        $this->assertEquals($expectedValues['waterLevel'], $gaugingStationRecord->getWaterLevel());
        $this->assertEquals($expectedValues['recordedDate'], $gaugingStationRecord->getRecordedAt());
    }

    /**
     * @return mixed[]
     */
    public function gaugingStations(): array
    {
        $waterTemperatures = [
            '29/05/2020' => [
                'р.Обь - г. Новосибирск' => '20.2', // р.Обь, Обская ГМО
                'Новосибирское водохранилище' => '19.5', // вдхр. Обское водохранилище, Обская ГМО (верхний бьеф)
            ],
        ];

        return [
            [
                'weather' => [
                    [
                        'name' => 'Обская ГМО',
                        'index' => '29635',
                        'center' => '54.850000, 82.966667',
                        'gidro' => [
                            [
                                'date' => '29.05.2020 срок 14 ВСВ',
                                'name' => 'р.Обь',
                                'data' => '239см -26см ',
                            ],
                        ],
                    ],
                ],
                'waterTemperatures' => $waterTemperatures,
                'expectedValues' => [
                    'stationName' => 'Обская ГМО',
                    'stationId' => '29635',
                    'stationCoordinates' => [
                        'latitude' => 54.85,
                        'longitude' => 82.966667,
                    ],
                    'temperature' => 20.2,
                    'waterName' => 'Обь',
                    'waterType' => WaterType::river(),
                    'waterLevel' => 239,
                    'recordedDate' => new DateTime('29.05.2020 14:00:00', new DateTimeZone('UTC')),
                ],
            ],
            [
                'weather' => [
                    [
                        'name' => 'Обская ГМО',
                        'index' => '29635',
                        'center' => '54.850000, 82.966667',
                        'gidro' => [
                            [
                                'date' => '29.05.2020 срок 00 ВСВ',
                                'name' => 'Верхний бьеф (вдхр)',
                                'data' => '239см -26см ',
                            ],
                        ],
                    ],
                ],
                'waterTemperatures' => $waterTemperatures,
                'expectedValues' => [
                    'stationName' => 'Обская ГМО (верхний бьеф)',
                    'stationId' => '29635',
                    'stationCoordinates' => [
                        'latitude' => 54.85,
                        'longitude' => 82.966667,
                    ],
                    'temperature' => 19.5,
                    'waterName' => 'Обское водохранилище',
                    'waterType' => WaterType::reservoir(),
                    'waterLevel' => 239,
                    'recordedDate' => new DateTime('29.05.2020 00:00:00', new DateTimeZone('UTC')),
                ],
            ],
            [
                'weather' => [
                    [
                        'name' => 'Артыбаш',
                        'index' => '10904',
                        'center' => '51.789821, 87.250173',
                        'gidro' => [
                            [
                                'date' => '29.05.2020 срок 08 ВСВ',
                                'name' => 'о.Телецкое',
                                'data' => '367см -5см ',
                            ],
                        ],
                    ],
                ],
                'waterTemperatures' => $waterTemperatures,
                'expectedValues' => [
                    'stationName' => 'Артыбаш',
                    'stationId' => '10904',
                    'stationCoordinates' => [
                        'latitude' => 51.789821,
                        'longitude' => 87.250173,
                    ],
                    'temperature' => null,
                    'waterName' => 'Телецкое',
                    'waterType' => WaterType::lake(),
                    'waterLevel' => 367,
                    'recordedDate' => new DateTime('29.05.2020 08:00:00', new DateTimeZone('UTC')),
                ],
            ],
            [
                'weather' => [
                    [
                        'name' => 'Остров Дальний',
                        'index' => '29723',
                        'center' => '54.466667, 82.300000',
                        'gidro' => [
                            [
                                'date' => '29.05.2020 срок 10 ВСВ',
                                'name' => 'вдхр.Обское водохранилище',
                                'data' => '558см +2см ',
                            ],
                        ],
                    ],
                ],
                'waterTemperatures' => $waterTemperatures,
                'expectedValues' => [
                    'stationName' => 'Остров Дальний',
                    'stationId' => '29723',
                    'stationCoordinates' => [
                        'latitude' => 54.466667,
                        'longitude' => 82.3,
                    ],
                    'temperature' => null,
                    'waterName' => 'Обское водохранилище',
                    'waterType' => WaterType::reservoir(),
                    'waterLevel' => 558,
                    'recordedDate' => new DateTime('29.05.2020 10:00:00', new DateTimeZone('UTC')),
                ],
            ],
        ];
    }

    /**
     * @param mixed[] $weather
     * @param mixed[] $waterTemperatures
     */
    private function createMeteoNsoClientMock(array $weather, array $waterTemperatures): MeteoNsoClient
    {
        $stub = $this->createMock(MeteoNsoClient::class);
        $stub
            ->method('getWeather')
            ->willReturn($weather);
        $stub
            ->method('getWaterTemperatures')
            ->willReturn($waterTemperatures);

        return $stub;
    }

    private function createGaugingStationRecordsDenormalizer(): GaugingStationRecordsDenormalizer
    {
        $gaugingStationRecordNameAnalyzer = new GaugingStationRecordNameAnalyzer();

        return new GaugingStationRecordsDenormalizer(
            new GaugingStationDenormalizer($gaugingStationRecordNameAnalyzer),
            new WaterDenormalizer($gaugingStationRecordNameAnalyzer),
            $this->createMock(LoggerInterface::class),
        );
    }
}
