<?php

namespace Tests\Functional\Domain\WaterLevel\Command\Handler;

use App\Domain\WaterLevel\Command\ShowGaugingStationCommand;
use App\Domain\WaterLevel\Entity\GaugingStation;
use Tests\Functional\TestCase;
use Tests\DataFixtures\ORM\WaterLevel\LoadHideBerdskGaugingStation;

/**
 * @group water-level
 */
class ShowGaugingStationHandlerTest extends TestCase
{
    public function testHandle(): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadHideBerdskGaugingStation::class,
        ])->getReferenceRepository();

        /** @var GaugingStation $gaugingStation */
        $gaugingStation = $referenceRepository->getReference(LoadHideBerdskGaugingStation::REFERENCE_NAME);

        $command = new ShowGaugingStationCommand($gaugingStation);

        $this->getCommandBus()->handle($command);

        $gaugingStationRepository = $this->getEntityManager()->getRepository(GaugingStation::class);
        /** @var GaugingStation $gaugingStation */
        $gaugingStation = $gaugingStationRepository->find($command->getGaugingStation()->getId());

        $this->assertFalse($gaugingStation->isHidden());
    }
}
