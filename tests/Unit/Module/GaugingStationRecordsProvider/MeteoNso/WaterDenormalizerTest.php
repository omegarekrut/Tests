<?php

namespace Tests\Unit\Module\GaugingStationRecordsProvider\MeteoNso;

use App\Module\GaugingStationRecordsProvider\MeteoNso\Exception\WaterNameIsEmptyException;
use App\Module\GaugingStationRecordsProvider\MeteoNso\Exception\WaterTypeIsEmptyException;
use App\Module\GaugingStationRecordsProvider\MeteoNso\Exception\WaterTypeIsNotSupportedException;
use App\Module\GaugingStationRecordsProvider\MeteoNso\GaugingStationRecordNameAnalyzer;
use App\Module\GaugingStationRecordsProvider\MeteoNso\WaterDenormalizer;
use App\Module\GaugingStationRecordsProvider\TransferObject\WaterType;
use Tests\Unit\TestCase;

/**
 * @group water-level
 */
class WaterDenormalizerTest extends TestCase
{
    /** @var WaterDenormalizer */
    private $waterDenormalizer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->waterDenormalizer = new WaterDenormalizer(new GaugingStationRecordNameAnalyzer());
    }

    protected function tearDown(): void
    {
        unset($this->waterDenormalizer);

        parent::tearDown();
    }

    public function testDenormalizeRiver(): void
    {
        $rawGaugingStationRecord = [
            'name' => 'р.Обь',
        ];

        $water = $this->waterDenormalizer->denormalize($rawGaugingStationRecord);

        $this->assertEquals('Обь', $water->getName());
        $this->assertEquals(WaterType::river(), $water->getType());
    }

    public function testDenormalizeLake(): void
    {
        $rawGaugingStationRecord = [
            'name' => 'о.Телецкое',
        ];

        $water = $this->waterDenormalizer->denormalize($rawGaugingStationRecord);

        $this->assertEquals('Телецкое', $water->getName());
        $this->assertEquals(WaterType::lake(), $water->getType());
    }

    public function testDenormalizeReservoir(): void
    {
        $rawGaugingStationRecord = [
            'name' => 'вдхр.Обское',
        ];

        $water = $this->waterDenormalizer->denormalize($rawGaugingStationRecord);

        $this->assertEquals('Обское', $water->getName());
        $this->assertEquals(WaterType::reservoir(), $water->getType());
    }

    public function testDenormalizeWithEmptyName(): void
    {
        $this->expectException(WaterNameIsEmptyException::class);

        $rawGaugingStationRecord = [
            'name' => 'some water',
        ];

        $this->waterDenormalizer->denormalize($rawGaugingStationRecord);
    }

    public function testDenormalizeWithEmptyType(): void
    {
        $this->expectException(WaterTypeIsEmptyException::class);

        $rawGaugingStationRecord = [
            'name' => '.some water',
        ];

        $this->waterDenormalizer->denormalize($rawGaugingStationRecord);
    }

    public function testDenormalizeWithUnsupportedType(): void
    {
        $this->expectException(WaterTypeIsNotSupportedException::class);

        $rawGaugingStationRecord = [
            'name' => 'some_type.some water',
        ];

        $this->waterDenormalizer->denormalize($rawGaugingStationRecord);
    }

    public function testDenormalizeWithReplaceableName(): void
    {
        $rawGaugingStationRecord = [
            'name' => 'вдхр',
        ];

        $water = $this->waterDenormalizer->denormalize($rawGaugingStationRecord);

        $this->assertEquals('Обское водохранилище', $water->getName());
        $this->assertEquals(WaterType::reservoir(), $water->getType());
    }

    public function testWaterNameMustBeDenormalizedWithoutSensorName(): void
    {
        $rawGaugingStationRecord = [
            'name' => 'Нижний бьеф (р.Обь)',
        ];

        $water = $this->waterDenormalizer->denormalize($rawGaugingStationRecord);

        $this->assertEquals('Обь', $water->getName());
    }
}
