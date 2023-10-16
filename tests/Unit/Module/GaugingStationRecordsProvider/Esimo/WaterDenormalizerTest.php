<?php

namespace Tests\Unit\Module\GaugingStationRecordsProvider\Esimo;

use App\Module\GaugingStationRecordsProvider\Esimo\WaterDenormalizer;
use App\Module\GaugingStationRecordsProvider\Esimo\WaterNameAnalyzer;
use App\Module\GaugingStationRecordsProvider\TransferObject\WaterType;
use Tests\Unit\TestCase;

class WaterDenormalizerTest extends TestCase
{
    private const GAUGING_STATION_NAME_COLUMN = 1;
    private const WATER_NAME_COLUMN = 20;
    private const HYDROLOGICAL_KNOWLEDGE_CODE_COLUMN = 19;

    private WaterDenormalizer $waterDenormalizer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->waterDenormalizer = new WaterDenormalizer(new WaterNameAnalyzer());
    }

    protected function tearDown(): void
    {
        unset($this->waterDenormalizer);

        parent::tearDown();
    }

    public function testDenormalize(): void
    {
        $record = [
            self::GAUGING_STATION_NAME_COLUMN => 'some value',
            self::WATER_NAME_COLUMN => 'р.СУРА',
            self::HYDROLOGICAL_KNOWLEDGE_CODE_COLUMN => '1',
        ];

        $water = $this->waterDenormalizer->denormalize($record);

        $this->assertEquals('Сура', $water->getName());
        $this->assertEquals(WaterType::river(), $water->getType());
    }

    public function testDenormalizeWithNameFromGaugingStationName(): void
    {
        $record = [
            self::GAUGING_STATION_NAME_COLUMN => 'г.Гомель,река Сож',
            self::WATER_NAME_COLUMN => '',
            self::HYDROLOGICAL_KNOWLEDGE_CODE_COLUMN => '1',
        ];

        $water = $this->waterDenormalizer->denormalize($record);

        $this->assertEquals('Сож', $water->getName());
        $this->assertEquals(WaterType::river(), $water->getType());
    }
}
