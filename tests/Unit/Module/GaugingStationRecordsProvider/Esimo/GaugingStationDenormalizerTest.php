<?php

namespace Tests\Unit\Module\GaugingStationRecordsProvider\Esimo;

use App\Module\GaugingStationRecordsProvider\Esimo\GaugingStationDenormalizer;
use App\Module\GaugingStationRecordsProvider\Esimo\GaugingStationNameAnalyzer;
use Tests\Unit\TestCase;

class GaugingStationDenormalizerTest extends TestCase
{
    private const GAUGING_STATION_ID_COLUMN = 0;
    private const GAUGING_STATION_NAME_COLUMN = 1;
    private const GAUGING_STATION_LATITUDE_COLUMN = 2;
    private const GAUGING_STATION_LONGITUDE_COLUMN = 3;

    public function testDenormalize(): void
    {
        $denormalizer = new GaugingStationDenormalizer(new GaugingStationNameAnalyzer());

        $record = [
            self::GAUGING_STATION_ID_COLUMN => '1234',
            self::GAUGING_STATION_NAME_COLUMN => 'г.ГОМЕЛЬ,река Сож',
            self::GAUGING_STATION_LATITUDE_COLUMN => '52.4',
            self::GAUGING_STATION_LONGITUDE_COLUMN => '30.95'
        ];

        $gaugingStation = $denormalizer->denormalize($record);

        $this->assertEquals('1234', $gaugingStation->getId());
        $this->assertEquals('Гомель', $gaugingStation->getName());
        $this->assertEquals(52.4, $gaugingStation->getGeographicalPosition()->getCoordinates()->getLatitude());
        $this->assertEquals(30.95, $gaugingStation->getGeographicalPosition()->getCoordinates()->getLongitude());
    }
}
