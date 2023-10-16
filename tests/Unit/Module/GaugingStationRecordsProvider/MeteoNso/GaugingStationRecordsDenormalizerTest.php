<?php

namespace Tests\Unit\Module\GaugingStationRecordsProvider\MeteoNso;

use App\Module\GaugingStationRecordsProvider\MeteoNso\GaugingStationDenormalizer;
use App\Module\GaugingStationRecordsProvider\MeteoNso\GaugingStationRecordNameAnalyzer;
use App\Module\GaugingStationRecordsProvider\MeteoNso\GaugingStationRecordsDenormalizer;
use App\Module\GaugingStationRecordsProvider\MeteoNso\WaterDenormalizer;
use App\Module\GaugingStationRecordsProvider\TransferObject\GaugingStationRecord;
use App\Module\GaugingStationRecordsProvider\TransferObject\WaterType;
use App\Util\Coordinates\Coordinates;
use DateTime;
use DateTimeZone;
use Psr\Log\LoggerInterface;
use Tests\Unit\TestCase;

/**
 * @group water-level
 */
class GaugingStationRecordsDenormalizerTest extends TestCase
{
    public function testDenormalize(): void
    {
        $gaugingStationRecordNameAnalyzer = new GaugingStationRecordNameAnalyzer();
        $gaugingStationRecordsDenormalizer = new GaugingStationRecordsDenormalizer(
            new GaugingStationDenormalizer($gaugingStationRecordNameAnalyzer),
            new WaterDenormalizer($gaugingStationRecordNameAnalyzer),
            $this->createMock(LoggerInterface::class),
        );

        $weatherRecord = [
            'name' => 'Обская ГМО',
            'index' => '29635',
            'center' => '54.850000, 82.966667',
            'temperature' => '19.0', // it is air temperature
            'gidro' => [
                [
                    'date' => '29.05.2020 срок 00 ВСВ',
                    'name' => 'р.Обь',
                    'data' => '239см -26см ',
                ],
            ],
        ];

        $waterTemperatureRecords = [
            '29/05/2020' => [
                'р.Обь - г. Новосибирск' => '20.2',
            ],
        ];

        $gaugingStationRecord = $gaugingStationRecordsDenormalizer->denormalize($weatherRecord, $waterTemperatureRecords)->current();
        assert($gaugingStationRecord instanceof GaugingStationRecord);

        $gaugingStationProvider = $gaugingStationRecord->getGaugingStationProvider();
        $water = $gaugingStationRecord->getWater();

        $this->assertEquals('Обская ГМО', $gaugingStationProvider->getName());
        $this->assertEquals('29635', $gaugingStationProvider->getId());
        $this->assertTrue($gaugingStationProvider->getGeographicalPosition()->getCoordinates()->equals(new Coordinates(
            54.85,
            82.966667
        )));

        $this->assertEquals('Обь', $water->getName());
        $this->assertEquals(WaterType::river(), $water->getType());

        $this->assertEquals(239, $gaugingStationRecord->getWaterLevel());
        $this->assertEquals(20.2, $gaugingStationRecord->getTemperature());
        $this->assertEquals(new DateTime('29.05.2020 00:00:00', new DateTimeZone('UTC')), $gaugingStationRecord->getRecordedAt());
    }
}
