<?php

namespace Tests\Functional\Domain\WaterLevel\Command\Handler;

use App\Domain\WaterLevel\Command\RemoveViewedGaugingStationCommand;
use App\Domain\WaterLevel\Entity\GaugingStation;
use App\Domain\WaterLevel\GaugingStationViewer\GaugingStationViewerMock;
use Tests\DataFixtures\ORM\WaterLevel\LoadBerdskGaugingStation;
use Tests\Functional\TestCase;

/**
 * @group water-level
 */
class RemoveViewedGaugingStationHandlerTest extends TestCase
{
    public function testHandle(): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadBerdskGaugingStation::class,
        ])->getReferenceRepository();

        /** @var GaugingStation $gaugingStation */
        $gaugingStation = $referenceRepository->getReference(LoadBerdskGaugingStation::REFERENCE_NAME);

        $gaugingStationViewer = new GaugingStationViewerMock();
        $gaugingStationViewer->addViewedGaugingStation($gaugingStation);

        $command = new RemoveViewedGaugingStationCommand($gaugingStationViewer, $gaugingStation);

        $this->getCommandBus()->handle($command);

        $this->assertNotContains($gaugingStation, $gaugingStationViewer->getLatestViewedOrClosestGaugingStations(10));
    }
}
