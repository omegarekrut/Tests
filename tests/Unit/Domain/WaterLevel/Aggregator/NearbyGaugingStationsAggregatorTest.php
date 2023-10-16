<?php

namespace Tests\Unit\Domain\WaterLevel\Aggregator;

use App\Domain\WaterLevel\Aggregator\NearbyGaugingStationsAggregator;
use App\Domain\WaterLevel\Collection\GaugingStationCollection;
use App\Domain\WaterLevel\Entity\GaugingStation;
use App\Domain\WaterLevel\Entity\ValueObject\GeographicalPosition;
use App\Domain\WaterLevel\Entity\ValueObject\WaterType;
use App\Domain\WaterLevel\Entity\Water;
use App\Util\Coordinates\Coordinates;
use Ramsey\Uuid\Uuid;
use Tests\Unit\TestCase;

/**
 * @group water-level
 */
class NearbyGaugingStationsAggregatorTest extends TestCase
{
    private NearbyGaugingStationsAggregator $nearbyGaugingStationsAggregator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->nearbyGaugingStationsAggregator = new NearbyGaugingStationsAggregator();
    }

    public function testAggregate(): void
    {
        $gaugingStationCollection = new GaugingStationCollection([
            $this->createGaugingStationWithCoordinates('Барнаул', new Coordinates(1.0, 1.002)),
            $this->createHideGaugingStationWithCoordinates('Бердск', new Coordinates(1.0, 1.003)),
            $this->createGaugingStationWithCoordinates('Новосибирск', new Coordinates(1.0, 1.003)),
            $this->createHideGaugingStationWithCoordinates('Новосибирская ГЭС', new Coordinates(1.0, 1.1)),
        ]);

        $aggregatedByDistanceGaugingStations = $this->nearbyGaugingStationsAggregator->aggregate($gaugingStationCollection);

        $this->assertCount(1, $aggregatedByDistanceGaugingStations);

        $nearbyGaugingStationCollection = $aggregatedByDistanceGaugingStations[0];

        $this->assertCount(3, $nearbyGaugingStationCollection);
        $this->assertEquals($gaugingStationCollection[0], $nearbyGaugingStationCollection[0]->getGaugingStation());
        $this->assertEquals($gaugingStationCollection[1], $nearbyGaugingStationCollection[1]->getGaugingStation());
        $this->assertEquals($gaugingStationCollection[2], $nearbyGaugingStationCollection[2]->getGaugingStation());
    }

    private function createGaugingStationWithCoordinates(string $name, Coordinates $coordinates): GaugingStation
    {
        $water = $this->createWater('Обь');

        return new GaugingStation(
            Uuid::uuid4(),
            'short-uuid',
            '',
            $water,
            $name,
            new GeographicalPosition($coordinates, null, null, null),
        );
    }

    private function createHideGaugingStationWithCoordinates(string $name, Coordinates $coordinates): GaugingStation
    {
        $gaugingStation = $this->createGaugingStationWithCoordinates($name, $coordinates);

        $gaugingStation->hide();

        return $gaugingStation;
    }

    private function createWater(string $waterName): Water
    {
        return new Water(
            Uuid::uuid4(),
            '',
            $waterName,
            WaterType::river()
        );
    }
}
