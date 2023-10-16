<?php

namespace Tests\Unit\Module\GaugingStationRecordsProvider\Esimo;

use App\Domain\WaterLevel\Entity\ValueObject\GaugingStationRecordsProviderKey;
use App\Module\GaugingStationRecordsProvider\Esimo\EsimoClient;
use App\Module\GaugingStationRecordsProvider\Esimo\EsimoGaugingStationRecordsProvider;
use App\Module\GaugingStationRecordsProvider\Esimo\GaugingStationDenormalizer;
use App\Module\GaugingStationRecordsProvider\Esimo\GaugingStationNameAnalyzer;
use App\Module\GaugingStationRecordsProvider\Esimo\GaugingStationRecordDenormalizer;
use App\Module\GaugingStationRecordsProvider\Esimo\WaterDenormalizer;
use App\Module\GaugingStationRecordsProvider\Esimo\WaterNameAnalyzer;
use App\Module\GaugingStationRecordsProvider\TransferObject\GaugingStationRecord;
use App\Module\GaugingStationRecordsProvider\TransferObject\WaterType;
use DateTime;
use Tests\Unit\LoggerMock;
use Tests\Unit\TestCase;

/**
 * @group water-level
 * @group gauging-station-records-provider
 */
class EsimoGaugingStationRecordsProviderTest extends TestCase
{
    private const GAUGING_STATION_ID_COLUMN = 0;
    private const GAUGING_STATION_NAME_COLUMN = 1;
    private const GAUGING_STATION_LATITUDE_COLUMN = 2;
    private const GAUGING_STATION_LONGITUDE_COLUMN = 3;
    private const DATE_COLUMN = 4;
    private const WATER_LEVEL_COLUMN = 5;
    private const TEMPERATURE_COLUMN = 8;
    private const WATER_NAME_COLUMN = 20;
    private const HYDROLOGICAL_KNOWLEDGE_CODE_COLUMN = 19;

    public function testGetProviderName(): void
    {
        $gaugingStationRecordsProvider = new EsimoGaugingStationRecordsProvider(
            $this->createMock(EsimoClient::class),
            $this->createMock(GaugingStationRecordDenormalizer::class),
            new LoggerMock()
        );

        $this->assertEquals(GaugingStationRecordsProviderKey::esimo(), $gaugingStationRecordsProvider->getProviderKey());
    }

    public function testProvide(): void
    {
        $records = [
            [
                self::GAUGING_STATION_ID_COLUMN => '79256',
                self::GAUGING_STATION_NAME_COLUMN => 'Гомель',
                self::GAUGING_STATION_LATITUDE_COLUMN => '52.4',
                self::GAUGING_STATION_LONGITUDE_COLUMN => '30.95',
                self::DATE_COLUMN => '2021-01-29 08:00:00',
                self::WATER_LEVEL_COLUMN => '115',
                self::TEMPERATURE_COLUMN => '1.5',
                self::WATER_NAME_COLUMN => 'река Сож',
                self::HYDROLOGICAL_KNOWLEDGE_CODE_COLUMN => '1',
            ],
        ];

        $provider = new EsimoGaugingStationRecordsProvider(
            $this->createEsimoClientMock($records),
            $this->createGaugingStationRecordDenormalizer(),
            new LoggerMock()
        );

        /** @var GaugingStationRecord $gaugingStationRecord */
        $gaugingStationRecord = $provider->provide()->current();

        $this->assertEquals('79256', $gaugingStationRecord->getGaugingStationProvider()->getId());
        $this->assertEquals('Гомель', $gaugingStationRecord->getGaugingStationProvider()->getName());
        $this->assertEquals(52.4, $gaugingStationRecord->getGaugingStationProvider()->getGeographicalPosition()->getCoordinates()->getLatitude());
        $this->assertEquals(30.95, $gaugingStationRecord->getGaugingStationProvider()->getGeographicalPosition()->getCoordinates()->getLongitude());
        $this->assertEquals((new DateTime('2021-01-29 08:00:00'))->getTimestamp(), $gaugingStationRecord->getRecordedAt()->getTimestamp());
        $this->assertEquals(115, $gaugingStationRecord->getWaterLevel());
        $this->assertEquals(1.5, $gaugingStationRecord->getTemperature());
        $this->assertEquals('Сож', $gaugingStationRecord->getWater()->getName());
        $this->assertEquals(WaterType::river(), $gaugingStationRecord->getWater()->getType());
    }

    public function testProvideWithSkippingInvalidRecords(): void
    {
        $records = [
            [
                self::GAUGING_STATION_ID_COLUMN => '79256',
                self::GAUGING_STATION_NAME_COLUMN => 'Гомель',
                self::GAUGING_STATION_LATITUDE_COLUMN => '52.4',
                self::GAUGING_STATION_LONGITUDE_COLUMN => '30.95',
                self::DATE_COLUMN => '2021-01-29 08:00:00',
                self::WATER_LEVEL_COLUMN => '115',
                self::TEMPERATURE_COLUMN => '1.5',
                self::WATER_NAME_COLUMN => 'invalid water name',
                self::HYDROLOGICAL_KNOWLEDGE_CODE_COLUMN => '1',
            ],
        ];

        $provider = new EsimoGaugingStationRecordsProvider(
            $this->createEsimoClientMock($records),
            $this->createGaugingStationRecordDenormalizer(),
            new LoggerMock()
        );

        $this->assertFalse($provider->provide()->valid());
    }

    private function createGaugingStationRecordDenormalizer(): GaugingStationRecordDenormalizer
    {
        $waterDenormalizer = new WaterDenormalizer(new WaterNameAnalyzer());
        $gaugingStationDenormalizer = new GaugingStationDenormalizer(new GaugingStationNameAnalyzer());

        return new GaugingStationRecordDenormalizer($gaugingStationDenormalizer, $waterDenormalizer);
    }

    /**
     * @param mixed[] $records
     */
    private function createEsimoClientMock(array $records): EsimoClient
    {
        $client = $this->createMock(EsimoClient::class);

        $recordGenerator = static function () use ($records) {
            yield from $records;
        };

        $client->method('getRecords')->willReturn($recordGenerator());

        return $client;
    }
}
