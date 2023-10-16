<?php

namespace Tests\Functional\Domain\WaterLevel\Repository;

use App\Domain\WaterLevel\Entity\GaugingStation;
use App\Domain\WaterLevel\Repository\GaugingStationRepository;
use App\Domain\WaterLevel\Search\GaugingStationSearchData;
use Tests\DataFixtures\ORM\WaterLevel\LoadBerdskGaugingStation;
use Tests\DataFixtures\ORM\WaterLevel\LoadInactiveBarnaulGaugingStation;
use Tests\DataFixtures\ORM\WaterLevel\LoadNovosibirskHydroelectricPowerStationGaugingStation;
use Tests\Functional\TestCase;

/**
 * @group water-level
 */
class GaugingStationRepositoryTest extends TestCase
{
    /** @var GaugingStationRepository */
    private $gaugingStationRepository;

    /** @var GaugingStation */
    private $barnaulGaugingStation;

    /** @var GaugingStation */
    private $berdskGaugingStation;

    protected function setUp(): void
    {
        parent::setUp();

        /** @var GaugingStationRepository $gaugingStationRepository */
        $this->gaugingStationRepository = $this->getContainer()->get(GaugingStationRepository::class);

        $referenceRepository = $this->loadFixtures([
            LoadInactiveBarnaulGaugingStation::class,
            LoadBerdskGaugingStation::class,
            LoadNovosibirskHydroelectricPowerStationGaugingStation::class, // used to increase the number of stations on ob
        ])->getReferenceRepository();

        $this->barnaulGaugingStation = $referenceRepository->getReference(LoadInactiveBarnaulGaugingStation::REFERENCE_NAME);
        $this->berdskGaugingStation = $referenceRepository->getReference(LoadBerdskGaugingStation::REFERENCE_NAME);
    }

    protected function tearDown(): void
    {
        unset(
            $this->berdskGaugingStation,
            $this->barnaulGaugingStation,
            $this->gaugingStationRepository
        );

        parent::tearDown();
    }

    public function testQueryBuilderMustBeBuiltWithoutAccessoryToWaterForEmptySearchData(): void
    {
        $allStationsCount = $this->gaugingStationRepository->count();

        $actualStations = $this->gaugingStationRepository
            ->createQueryBuilderWithFilterBySearchData(new GaugingStationSearchData())
            ->getQuery()
            ->getResult();

        $this->assertCount($allStationsCount, $actualStations);
    }

    public function testQueryBuilderMustBeReadyFindOnlyStationsOnWaterForPredefinedWaterInSearchData(): void
    {
        $berd = $this->berdskGaugingStation->getWater();

        $stationsOnBerdSearchData = new GaugingStationSearchData();
        $stationsOnBerdSearchData->water = $berd;

        $actualStations = $this->gaugingStationRepository
            ->createQueryBuilderWithFilterBySearchData($stationsOnBerdSearchData)
            ->getQuery()
            ->getResult();

        $this->assertCount(count($berd->getShownGaugingStations()), $actualStations);

        foreach ($berd->getShownGaugingStations() as $berdStation) {
            $this->assertContains($berdStation, $actualStations);
        }
    }
}
