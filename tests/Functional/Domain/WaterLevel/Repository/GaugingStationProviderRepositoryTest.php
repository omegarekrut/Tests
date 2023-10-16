<?php

namespace Tests\Functional\Domain\WaterLevel\Repository;

use App\Domain\WaterLevel\Repository\GaugingStationProviderRepository;
use Tests\DataFixtures\ORM\WaterLevel\LoadGaugingStationProviderWithoutGaugingStation;
use Tests\DataFixtures\ORM\WaterLevel\LoadNovosibirskGaugingStation;
use Tests\Functional\TestCase;

class GaugingStationProviderRepositoryTest extends TestCase
{
    private GaugingStationProviderRepository $gaugingStationProviderRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->gaugingStationProviderRepository = $this->getContainer()->get(GaugingStationProviderRepository::class);
    }

    public function testFindAllByGaugingStation(): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadGaugingStationProviderWithoutGaugingStation::class,
            LoadNovosibirskGaugingStation::class,
        ])->getReferenceRepository();

        $gaugingStationProviderWithoutGaugingStation = $referenceRepository->getReference(LoadGaugingStationProviderWithoutGaugingStation::REFERENCE_NAME);
        $gaugingStation = $referenceRepository->getReference(LoadNovosibirskGaugingStation::REFERENCE_NAME);

        $gaugingStationProvidersForGaugingStation = $this->gaugingStationProviderRepository->findAllByGaugingStation($gaugingStation);

        $this->assertContains($gaugingStationProviderWithoutGaugingStation, $gaugingStationProvidersForGaugingStation);

        foreach ($gaugingStation->getGaugingStationProviders() as $gaugingStationProviderByGaugingStation) {
            $this->assertContains($gaugingStationProviderByGaugingStation, $gaugingStationProvidersForGaugingStation);
        }
    }
}
