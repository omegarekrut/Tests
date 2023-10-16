<?php

namespace Tests\Unit\Module\GaugingStationRecordsProvider\MeteoNso;

use App\Module\GaugingStationRecordsProvider\MeteoNso\Exception\GaugingStationIdIsNotDefinedException;
use App\Module\GaugingStationRecordsProvider\MeteoNso\GaugingStationDenormalizer;
use App\Module\GaugingStationRecordsProvider\MeteoNso\GaugingStationRecordNameAnalyzer;
use App\Util\Coordinates\Coordinates;
use Tests\Unit\TestCase;

/**
 * @group water-level
 */
class GaugingStationDenormalizerTest extends TestCase
{
    private GaugingStationDenormalizer $gaugingStationDenormalizer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->gaugingStationDenormalizer = new GaugingStationDenormalizer(new GaugingStationRecordNameAnalyzer());
    }

    protected function tearDown(): void
    {
        unset($this->gaugingStationDenormalizer);

        parent::tearDown();
    }

    public function testDenormalize(): void
    {
        $weatherRecord = [
            'index' => 1,
            'name' => 'Name',
            'center' => '56.4345, 14.3434',
        ];

        $gaugingStation = $this->gaugingStationDenormalizer->denormalize($weatherRecord, []);

        $this->assertEquals(1, $gaugingStation->getId());
        $this->assertEquals('Name', $gaugingStation->getName());
        $this->assertTrue($gaugingStation->getGeographicalPosition()->getCoordinates()->equals(new Coordinates(
            56.4345,
            14.3434
        )));
    }

    public function testDenormalizeWithUnderscore(): void
    {
        $weatherRecord = [
            'index' => 1,
            'name' => 'Some_name',
            'center' => '56.4345, 14.3434',
        ];

        $gaugingStation = $this->gaugingStationDenormalizer->denormalize($weatherRecord, []);

        $this->assertEquals(1, $gaugingStation->getId());
        $this->assertEquals('Some name', $gaugingStation->getName());
        $this->assertTrue($gaugingStation->getGeographicalPosition()->getCoordinates()->equals(new Coordinates(
            56.4345,
            14.3434
        )));
    }

    public function testGaugingStationNameMustContainsSensorNameIfExists(): void
    {
        $weatherRecord = [
            'index' => 1,
            'name' => 'Name',
            'center' => '56.4345, 14.3434',
        ];

        $rawGaugingStationRecord = [
            'name' => 'р.River (first sensor)',
        ];

        $gaugingStation = $this->gaugingStationDenormalizer->denormalize($weatherRecord, $rawGaugingStationRecord);

        $this->assertEquals(1, $gaugingStation->getId());
        $this->assertEquals('Name (first sensor)', $gaugingStation->getName());
    }

    public function testDenoramalizationMustFailForUnidentifiedStation(): void
    {
        $this->expectException(GaugingStationIdIsNotDefinedException::class);

        $weatherRecord = [
            'index' => null,
        ];

        $this->gaugingStationDenormalizer->denormalize($weatherRecord, []);
    }
}
