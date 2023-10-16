<?php

namespace Tests\Functional\Domain\WaterLevel\Command\Handler;

use App\Domain\WaterLevel\Command\RemoveGaugingStationCommand;
use App\Domain\WaterLevel\Entity\GaugingStation;
use Tests\DataFixtures\ORM\WaterLevel\LoadGaugingStationWithoutProviders;
use Tests\Functional\TestCase;

class RemoveGaugingStationHandlerTest extends TestCase
{
    public function testHandle(): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadGaugingStationWithoutProviders::class,
        ])->getReferenceRepository();

        $gaugingStation = $referenceRepository->getReference(LoadGaugingStationWithoutProviders::REFERENCE_NAME);
        assert($gaugingStation instanceof GaugingStation);

        $command = new RemoveGaugingStationCommand($gaugingStation->getId());

        $this->getCommandBus()->handle($command);

        $gaugingStationRepository = $this->getEntityManager()->getRepository(GaugingStation::class);

        $this->assertEmpty($gaugingStationRepository->find($gaugingStation));
    }
}
