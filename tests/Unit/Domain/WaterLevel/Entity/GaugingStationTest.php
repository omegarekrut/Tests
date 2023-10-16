<?php

namespace Tests\Unit\Domain\WaterLevel\Entity;

use App\Domain\WaterLevel\Entity\GaugingStation;
use App\Domain\WaterLevel\Entity\GaugingStationProvider;
use App\Domain\WaterLevel\Entity\ValueObject\ExternalIdentifier;
use App\Domain\WaterLevel\Entity\ValueObject\GeographicalPosition;
use App\Domain\WaterLevel\Entity\Water;
use App\Util\Coordinates\Coordinates;
use Ramsey\Uuid\Uuid;
use Tests\Unit\TestCase;
use DateTime;

class GaugingStationTest extends TestCase
{
    private const GAUGING_STATION_DISTANCE_FROM_SOURCE = 100;
    private const DISTANCE_FROM_SOURCE_OF_PARENT_WATER = 200;

    public function testIsActiveGaugingStation(): void
    {
        $lastRecordDate = new DateTime();
        $lastRecordDate->sub(new \DateInterval('P9DT23H59M'));

        $gaugingStationProvider = $this->createGaugingStationProvider();

        $gaugingStation = $gaugingStationProvider->getGaugingStation();

        $gaugingStationProvider->addRecord(Uuid::uuid4(), $lastRecordDate, 0, 0);
        $gaugingStation->rewriteLatestRecord($gaugingStationProvider->getRecords()->last());

        $this->assertTrue($gaugingStation->isActive());
    }

    public function testIsNotActiveGaugingStationWithoutProvider(): void
    {
        $gaugingStation = new GaugingStation(
            Uuid::uuid4(),
            'short-uuid',
            'slug',
            $this->createMock(Water::class),
            'name',
            $this->createMock(GeographicalPosition::class),
        );

        $this->assertFalse($gaugingStation->isActive());
    }

    public function testIsNotActiveGaugingStation(): void
    {
        $lastRecordDate = new DateTime();
        $lastRecordDate->sub(new \DateInterval('P10D'));

        $gaugingStationProvider = $this->createGaugingStationProvider();
        $gaugingStationProvider->addRecord(Uuid::uuid4(), $lastRecordDate, 0, 0);

        $this->assertFalse($gaugingStationProvider->getGaugingStation()->isActive());
    }

    public function testIsNotActiveGaugingStationWithoutRecords(): void
    {
        $gaugingStationProvider = $this->createGaugingStationProvider();

        $this->assertFalse($gaugingStationProvider->getGaugingStation()->isActive());
    }

    public function testGetDistanceFromSource(): void
    {
        $gaugingStationProvider = $this->createGaugingStationProvider();

        $this->assertEquals(self::GAUGING_STATION_DISTANCE_FROM_SOURCE, $gaugingStationProvider->getGaugingStation()->getDistanceFromSource());
    }

    public function testGaugingStationOfNestedWaterGetDistanceFromSource(): void
    {
        $gaugingStation = $this->getGaugingStationOfNestedWater();
        $parentWater = $gaugingStation->getWater()->getParentWater();

        $this->assertEquals(
            self::GAUGING_STATION_DISTANCE_FROM_SOURCE + self::DISTANCE_FROM_SOURCE_OF_PARENT_WATER,
            $gaugingStation->getDistanceFromSourceOfAncestorWater($parentWater)
        );
    }

    private function getGaugingStationOfNestedWater(): GaugingStation
    {
        $parentWater = $this->createMock(Water::class);

        return new GaugingStation(
            Uuid::uuid4(),
            'short-uuid',
            '',
            $this->createConfiguredMock(Water::class, [
                'getParentWater' => $parentWater,
                'getDistanceFromParentWaterSourceInKilometers' => (float) 200,
            ]),
            '',
            new GeographicalPosition($this->createMock(Coordinates::class), 100, 0, 0),
        );
    }

    /**
     * @see https://resolventa.atlassian.net/browse/FS-3093
     */
    private function createGaugingStationProvider(): GaugingStationProvider
    {
        $gaugingStation = new GaugingStation(
            Uuid::uuid4(),
            'short-uuid',
            '',
            $this->createMock(Water::class),
            '',
            new GeographicalPosition($this->createMock(Coordinates::class), self::GAUGING_STATION_DISTANCE_FROM_SOURCE, 0, 0),
        );

        $gaugingStationProvider = new GaugingStationProvider(
            Uuid::uuid4(),
            $this->createMock(ExternalIdentifier::class),
            new GeographicalPosition($this->createMock(Coordinates::class), self::GAUGING_STATION_DISTANCE_FROM_SOURCE, 0, 0),
            'River name',
        );

        $gaugingStation->addGaugingStationProvider($gaugingStationProvider);
        $gaugingStationProvider->setGaugingStation($gaugingStation);

        return $gaugingStationProvider;
    }
}
