<?php

namespace Tests\Unit\Domain\WaterLevel\Collection;

use App\Domain\WaterLevel\Aggregator\TransferObject\NearbyGaugingStation;
use App\Domain\WaterLevel\Collection\NearbyGaugingStationCollection;
use App\Domain\WaterLevel\Collection\NearbyGaugingStationsGroupsCollection;
use App\Domain\WaterLevel\Entity\GaugingStation;
use App\Domain\WaterLevel\Entity\ValueObject\GeographicalPosition;
use App\Domain\WaterLevel\Entity\ValueObject\WaterType;
use App\Domain\WaterLevel\Entity\Water;
use App\Util\Coordinates\Coordinates;
use Tests\Unit\TestCase;
use Ramsey\Uuid\Uuid;

/**
 * @group water-level
 */
class NearbyGaugingStationsGroupsCollectionTest extends TestCase
{
    public function testFilterOnlyWithConflicts(): void
    {
        $nearbyGaugingStationsGroups = $this->createNearbyGaugingStationsGroupsCollection();

        $this->assertCount(5, $nearbyGaugingStationsGroups);

        $nearbyGaugingStationsGroupsWithConflict = $nearbyGaugingStationsGroups->filterOnlyWithConflicts();

        $this->assertCount(2, $nearbyGaugingStationsGroupsWithConflict);
        $this->assertContains($nearbyGaugingStationsGroups->get(2), $nearbyGaugingStationsGroupsWithConflict);
        $this->assertContains($nearbyGaugingStationsGroups->get(4), $nearbyGaugingStationsGroupsWithConflict);
    }

    public function testFilterOnlyWithoutConflicts(): void
    {
        $nearbyGaugingStationsGroups = $this->createNearbyGaugingStationsGroupsCollection();

        $this->assertCount(5, $nearbyGaugingStationsGroups);

        $nearbyGaugingStationsGroupsWithoutConflict = $nearbyGaugingStationsGroups->filterOnlyWithoutConflicts();

        $this->assertCount(2, $nearbyGaugingStationsGroupsWithoutConflict);
        $this->assertContains($nearbyGaugingStationsGroups->get(1), $nearbyGaugingStationsGroupsWithoutConflict);
        $this->assertContains($nearbyGaugingStationsGroups->get(3), $nearbyGaugingStationsGroupsWithoutConflict);
    }

    private function createNearbyGaugingStationsGroupsCollection(): NearbyGaugingStationsGroupsCollection
    {
        $nearbyGaugingStationsWithoutElement = new NearbyGaugingStationCollection([]);

        $nearbyGaugingStationsWithOneElement = new NearbyGaugingStationCollection([
            $this->createNearbyGaugingStation('Барнаул'),
        ]);

        $nearbyGaugingStationsWithTwoElement = new NearbyGaugingStationCollection([
            $this->createNearbyGaugingStation('Барнаул'),
            $this->createNearbyGaugingStation('Бердск'),
        ]);

        $nearbyGaugingStationsWithTwoElementAndOneHidden = new NearbyGaugingStationCollection([
            $this->createNearbyGaugingStation('Барнаул'),
            $this->createNearbyGaugingStationWithHiddenStation('Бердск'),
        ]);

        $nearbyGaugingStationsWithManyElement = new NearbyGaugingStationCollection([
            $this->createNearbyGaugingStation('Барнаул'),
            $this->createNearbyGaugingStation('Бердск'),
            $this->createNearbyGaugingStation('Новосибирск'),
            $this->createNearbyGaugingStation('Новосибирская ГЭС'),
        ]);

        return new NearbyGaugingStationsGroupsCollection([
            $nearbyGaugingStationsWithoutElement,
            $nearbyGaugingStationsWithOneElement,
            $nearbyGaugingStationsWithTwoElement,
            $nearbyGaugingStationsWithTwoElementAndOneHidden,
            $nearbyGaugingStationsWithManyElement,
        ]);
    }

    private function createNearbyGaugingStation(string $nameGaugingStation): NearbyGaugingStation
    {
        return new NearbyGaugingStation($this->createGaugingStation($nameGaugingStation), 0);
    }

    private function createNearbyGaugingStationWithHiddenStation(string $nameGaugingStation): NearbyGaugingStation
    {
        $gaugingStation = $this->createGaugingStation($nameGaugingStation);
        $gaugingStation->hide();

        return new NearbyGaugingStation($gaugingStation, 0);
    }

    private function createGaugingStation(string $name): GaugingStation
    {
        $water = $this->createWater('Обь');

        return new GaugingStation(
            Uuid::uuid4(),
            'short-uuid',
            '',
            $water,
            $name,
            new GeographicalPosition(new Coordinates(0.0, 0.0), null, null, null),
        );
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
