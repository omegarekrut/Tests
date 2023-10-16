<?php

namespace Tests\Unit\Domain\WaterLevel\Collection;

use App\Domain\WaterLevel\Aggregator\TransferObject\NearbyGaugingStation;
use App\Domain\WaterLevel\Collection\NearbyGaugingStationCollection;
use App\Domain\WaterLevel\Entity\GaugingStation;
use Tests\Unit\TestCase;

/**
 * @group water-level
 */
class NearbyGaugingStationCollectionTest extends TestCase
{
    public function testFilterOnlyDisplayedGaugingStations(): void
    {
        $mustBeFirst = $this->createNearbyGaugingStation(0, false);
        $mustBeSecond = $this->createNearbyGaugingStation(1.0, true);
        $mustBeThird = $this->createNearbyGaugingStation(1.1, false);

        $nearbyGaugingStationCollection = new NearbyGaugingStationCollection([
            $mustBeThird,
            $mustBeFirst,
            $mustBeSecond,
        ]);

        $filteredStations = $nearbyGaugingStationCollection->filterOnlyDisplayedGaugingStations();

        $this->assertCount(2, $filteredStations);
        $this->assertContains($mustBeFirst, $filteredStations);
        $this->assertContains($mustBeThird, $filteredStations);
        $this->assertNotContains($mustBeSecond, $filteredStations);
    }

    private function createNearbyGaugingStation(float $distanceInKilometer, bool $isHiddenStation): NearbyGaugingStation
    {
        $nearbyGaugingStation = $this->createMock(NearbyGaugingStation::class);
        $nearbyGaugingStation
            ->method('getDistanceInKilometer')
            ->willReturn($distanceInKilometer);

        $nearbyGaugingStation
            ->method('getGaugingStation')
            ->willReturn($this->createGaugingStation($isHiddenStation));

        return $nearbyGaugingStation;
    }

    private function createGaugingStation(bool $hidden): GaugingStation
    {
        $gaugingStation = $this->createMock(GaugingStation::class);
        $gaugingStation
            ->method('isHidden')
            ->willReturn($hidden);

        return $gaugingStation;
    }
}
