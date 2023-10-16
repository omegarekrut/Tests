<?php

namespace Tests\Unit\Module\GaugingStationRecordsProvider\Esimo;

use App\Module\GaugingStationRecordsProvider\Esimo\Exception\RecordDenormalizationIsImpossibleException;
use App\Module\GaugingStationRecordsProvider\Esimo\Exception\WaterNameIsEmptyException;
use App\Module\GaugingStationRecordsProvider\Esimo\Exception\WaterTypeIsNotSupportedException;
use App\Module\GaugingStationRecordsProvider\Esimo\GaugingStationDenormalizer;
use App\Module\GaugingStationRecordsProvider\Esimo\GaugingStationRecordDenormalizer;
use App\Module\GaugingStationRecordsProvider\Esimo\WaterDenormalizer;
use App\Module\GaugingStationRecordsProvider\TransferObject\GaugingStationProvider;
use App\Module\GaugingStationRecordsProvider\TransferObject\Water;
use DateTime;
use Throwable;
use Tests\Unit\TestCase;

class GaugingStationRecordDenormalizerTest extends TestCase
{
    private const WATER_LEVEL_COLUMN = 5;
    private const TEMPERATURE_COLUMN = 8;
    private const DATE_COLUMN = 4;

    public function testDenormalize(): void
    {
        $gaugingStationProvider = $this->createMock(GaugingStationProvider::class);
        $water = $this->createMock(Water::class);

        $recordDenormalizer = new GaugingStationRecordDenormalizer(
            $this->createGaugingStationProviderDenormalizerMock($gaugingStationProvider),
            $this->createWaterDenormalizerMock($water)
        );

        $record = [
            self::WATER_LEVEL_COLUMN => '97.0',
            self::TEMPERATURE_COLUMN => '1.2',
            self::DATE_COLUMN => '2021-01-28 06:00:00.0',
        ];

        $gaugingStationRecord = $recordDenormalizer->denormalize($record);

        $this->assertEquals($gaugingStationProvider, $gaugingStationRecord->getGaugingStationProvider());
        $this->assertEquals($water, $gaugingStationRecord->getWater());
        $this->assertEquals(97.0, $gaugingStationRecord->getWaterLevel());
        $this->assertEquals(1.2, $gaugingStationRecord->getTemperature());
        $this->assertEquals((new DateTime('2021-01-28 06:00:00.0'))->getTimestamp(), $gaugingStationRecord->getRecordedAt()->getTimestamp());
    }

    public function testDenormalizeWithoutTemperature(): void
    {
        $gaugingStationProvider = $this->createMock(GaugingStationProvider::class);
        $water = $this->createMock(Water::class);

        $recordDenormalizer = new GaugingStationRecordDenormalizer(
            $this->createGaugingStationProviderDenormalizerMock($gaugingStationProvider),
            $this->createWaterDenormalizerMock($water)
        );

        $record = [
            self::WATER_LEVEL_COLUMN => '97.0',
            self::TEMPERATURE_COLUMN => '',
            self::DATE_COLUMN => '2021-01-28 06:00:00.0',
        ];

        $gaugingStationRecord = $recordDenormalizer->denormalize($record);

        $this->assertEquals($gaugingStationProvider, $gaugingStationRecord->getGaugingStationProvider());
        $this->assertEquals($water, $gaugingStationRecord->getWater());
        $this->assertEquals(97.0, $gaugingStationRecord->getWaterLevel());
        $this->assertNull($gaugingStationRecord->getTemperature());
        $this->assertEquals((new DateTime('2021-01-28 06:00:00.0'))->getTimestamp(), $gaugingStationRecord->getRecordedAt()->getTimestamp());
    }

    public function testDenormalizeWithThrowingWaterNameIsEmptyException(): void
    {
        $gaugingStationProvider = $this->createMock(GaugingStationProvider::class);

        $recordDenormalizer = new GaugingStationRecordDenormalizer(
            $this->createGaugingStationProviderDenormalizerMock($gaugingStationProvider),
            $this->createWaterDenormalizerMockThrowingException(new WaterNameIsEmptyException())
        );

        $record = [
            self::WATER_LEVEL_COLUMN => '97.0',
            self::TEMPERATURE_COLUMN => '',
            self::DATE_COLUMN => '2021-01-28 06:00:00.0',
        ];

        $this->expectException(RecordDenormalizationIsImpossibleException::class);

        $recordDenormalizer->denormalize($record);
    }

    public function testDenormalizeWithThrowingWaterTypeIsNotSupportedException(): void
    {
        $gaugingStationProvider = $this->createMock(GaugingStationProvider::class);

        $recordDenormalizer = new GaugingStationRecordDenormalizer(
            $this->createGaugingStationProviderDenormalizerMock($gaugingStationProvider),
            $this->createWaterDenormalizerMockThrowingException(new WaterTypeIsNotSupportedException())
        );

        $record = [
            self::WATER_LEVEL_COLUMN => '97.0',
            self::TEMPERATURE_COLUMN => '',
            self::DATE_COLUMN => '2021-01-28 06:00:00.0',
        ];

        $this->expectException(RecordDenormalizationIsImpossibleException::class);

        $recordDenormalizer->denormalize($record);
    }

    private function createGaugingStationProviderDenormalizerMock(GaugingStationProvider $gaugingStationProvider): GaugingStationDenormalizer
    {
        $gaugingStationDenormalizer = $this->createMock(GaugingStationDenormalizer::class);

        $gaugingStationDenormalizer->method('denormalize')->willReturn($gaugingStationProvider);

        return $gaugingStationDenormalizer;
    }

    private function createWaterDenormalizerMock(Water $water): WaterDenormalizer
    {
        $waterDenormalizer = $this->createMock(WaterDenormalizer::class);

        $waterDenormalizer->method('denormalize')->willReturn($water);

        return $waterDenormalizer;
    }

    private function createWaterDenormalizerMockThrowingException(Throwable $exception): WaterDenormalizer
    {
        $waterDenormalizer = $this->createMock(WaterDenormalizer::class);

        $waterDenormalizer->method('denormalize')->willThrowException($exception);

        return $waterDenormalizer;
    }
}
