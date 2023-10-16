<?php

namespace Tests\Functional\Domain\WaterLevel\Command\Handler;

use App\Domain\WaterLevel\Command\CacheGaugingStationMapImageCommand;
use App\Domain\WaterLevel\Entity\GaugingStation;
use App\Domain\WaterLevel\Repository\GaugingStationRepository;
use App\Module\StaticMapImageLoader\Mock\StaticMapImageLoaderMock;
use Tests\DataFixtures\ORM\WaterLevel\LoadBerdskGaugingStation;
use Tests\Functional\TestCase;

class CacheGaugingStationMapImageHandlerTest extends TestCase
{
    private GaugingStationRepository $gaugingStationRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->gaugingStationRepository = $this->getContainer()->get(GaugingStationRepository::class);
    }

    protected function tearDown(): void
    {
        unset($this->gaugingStationRepository);

        parent::tearDown();
    }

    public function testHandle(): void
    {
        $referenceRepository = $this->loadFixtures([LoadBerdskGaugingStation::class])->getReferenceRepository();
        /** @var GaugingStation $gaugingStation */
        $gaugingStation = $referenceRepository->getReference(LoadBerdskGaugingStation::REFERENCE_NAME);
        $command = new CacheGaugingStationMapImageCommand($gaugingStation->getId());

        $this->getCommandBus()->handle($command);

        $this->getEntityManager()->clear();

        $actualGaugingStation = $this->gaugingStationRepository->find($gaugingStation->getId());

        $this::assertEquals(
            StaticMapImageLoaderMock::STATIC_MAP_IMAGE_FILENAME,
            $actualGaugingStation->getGeographicalPosition()->getStaticMapImage()->getFilename()
        );
    }
}
