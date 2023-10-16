<?php

namespace Tests\Functional\Domain\WaterLevel\Command\Handler;

use App\Domain\WaterLevel\Command\HideGaugingStationCommand;
use App\Domain\WaterLevel\Entity\GaugingStation;
use Tests\Functional\TestCase;
use Tests\DataFixtures\ORM\WaterLevel\LoadBerdskGaugingStation;

/**
 * @group water-level
 */
class HideGaugingStationHandlerTest extends TestCase
{
    public function testHandle(): void
    {
        $referenceRepository = $this->loadFixtures([
                LoadBerdskGaugingStation::class,
        ])->getReferenceRepository();

        /** @var GaugingStation $gaugingStation */
        $gaugingStation = $referenceRepository->getReference(LoadBerdskGaugingStation::REFERENCE_NAME);

        $command = new HideGaugingStationCommand($gaugingStation);

        $this->getCommandBus()->handle($command);

        $gaugingStationRepository = $this->getEntityManager()->getRepository(GaugingStation::class);
        /** @var GaugingStation $gaugingStation */
        $gaugingStation = $gaugingStationRepository->find($command->getGaugingStation()->getId());

        $this->assertTrue($gaugingStation->isHidden());
    }
}
