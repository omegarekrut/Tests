<?php

namespace Tests\Functional\Domain\WaterLevel\Command\Handler;

use App\Domain\WaterLevel\Command\AddViewedGaugingStationCommand;
use App\Domain\WaterLevel\Entity\GaugingStation;
use App\Domain\WaterLevel\GaugingStationViewer\GaugingStationViewerMock;
use Tests\DataFixtures\ORM\WaterLevel\LoadBerdskGaugingStation;
use Tests\Functional\TestCase;

/**
 * @group water-level
 */
class AddViewedGaugingStationHandlerTest extends TestCase
{
    public function testHandle(): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadBerdskGaugingStation::class,
        ])->getReferenceRepository();

        $gaugingStationViewer = new GaugingStationViewerMock();

        /** @var GaugingStation $newViewedGaugingStation */
        $newViewedGaugingStation = $referenceRepository->getReference(LoadBerdskGaugingStation::REFERENCE_NAME);

        $command = new AddViewedGaugingStationCommand($gaugingStationViewer, $newViewedGaugingStation);

        $this->getCommandBus()->handle($command);

        $this->assertContains($newViewedGaugingStation, $gaugingStationViewer->getLatestViewedOrClosestGaugingStations(10));
    }
}
