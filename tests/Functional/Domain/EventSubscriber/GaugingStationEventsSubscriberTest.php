<?php

namespace Tests\Functional\Domain\EventSubscriber;

use App\Domain\WaterLevel\Entity\GaugingStation;
use App\Domain\WaterLevel\Event\GaugingStationCreatedEvent;
use App\Domain\WaterLevel\Event\GaugingStationViewedEvent;
use App\Domain\WaterLevel\GaugingStationViewer\GaugingStationViewerMock;
use Tests\DataFixtures\ORM\WaterLevel\LoadBerdskGaugingStation;
use Tests\Functional\TestCase;

/**
 * @group water-level
 */
class GaugingStationEventsSubscriberTest extends TestCase
{
    private GaugingStation $gaugingStation;

    protected function setUp(): void
    {
        parent::setUp();

        $referenceRepository = $this->loadFixtures([
            LoadBerdskGaugingStation::class,
        ])->getReferenceRepository();

        $this->gaugingStation = $referenceRepository->getReference(LoadBerdskGaugingStation::REFERENCE_NAME);
    }

    protected function tearDown(): void
    {
        unset($this->gaugingStation);

        parent::tearDown();
    }

    public function testAddViewedGaugingStation(): void
    {
        $gaugingStationViewer = new GaugingStationViewerMock();

        $event = new GaugingStationViewedEvent($gaugingStationViewer, $this->gaugingStation);

        $this->getEventDispatcher()->dispatch($event);

        $this->assertContains($this->gaugingStation, $gaugingStationViewer->getLatestViewedOrClosestGaugingStations(10));
    }

    public function testLoadGaugingStationStaticMapImage(): void
    {
        $event = new GaugingStationCreatedEvent($this->gaugingStation->getId());

        $this->getEventDispatcher()->dispatch($event);

        $this->assertNotNull($this->gaugingStation->getGeographicalPosition()->getStaticMapImage());
    }
}
