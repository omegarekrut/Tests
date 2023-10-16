<?php

namespace Tests\Functional\Domain\WaterLevel\Command\Handler;

use App\Domain\WaterLevel\Command\CreateGaugingStationCommand;
use App\Domain\WaterLevel\Entity\GaugingStation;
use App\Domain\WaterLevel\Entity\GaugingStationProvider;
use App\Domain\WaterLevel\Entity\ValueObject\GeographicalPosition;
use App\Domain\WaterLevel\Entity\Water;
use App\Util\Coordinates\Coordinates;
use Ramsey\Uuid\Uuid;
use Tests\DataFixtures\ORM\WaterLevel\LoadGaugingStationProviderWithoutGaugingStation;
use Tests\DataFixtures\ORM\WaterLevel\LoadObWater;
use Tests\Functional\TestCase;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @group water-level
 */
class CreateGaugingStationHandlerTest extends TestCase
{
    public function testHandle(): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadObWater::class,
            LoadGaugingStationProviderWithoutGaugingStation::class,
        ])->getReferenceRepository();

        $water = $referenceRepository->getReference(LoadObWater::REFERENCE_NAME);
        assert($water instanceof Water);

        $gaugingStationProvider = $referenceRepository->getReference(LoadGaugingStationProviderWithoutGaugingStation::REFERENCE_NAME);
        assert($gaugingStationProvider instanceof GaugingStationProvider);

        $geographicalPosition = new GeographicalPosition(new Coordinates(80.4, 45.5), 100, 200, 4);

        $command = new CreateGaugingStationCommand(
            Uuid::uuid4(),
        );

        $command->water = $water;
        $command->name = 'Station name';
        $command->coordinates = new Coordinates(80.4, 45.5);
        $command->seaLevelHeight = 4.0;
        $command->distanceFromSourceInKilometers = 100;
        $command->distanceToEstuaryInKilometers = 200;
        $command->gaugingStationProviders = new ArrayCollection([$gaugingStationProvider]);

        $this->getCommandBus()->handle($command);

        $gaugingStationRepository = $this->getEntityManager()->getRepository(GaugingStation::class);
        $gaugingStation = $gaugingStationRepository->find($command->getId());
        assert($gaugingStation instanceof GaugingStation);

        $this->assertEquals($command->getId(), $gaugingStation->getId());
        $this->assertEquals($command->name, $gaugingStation->getName());
        $this->assertEquals($command->water, $gaugingStation->getWater());

        $this->assertEquals(
            $geographicalPosition->getSeaLevelHeight(),
            $gaugingStation->getGeographicalPosition()->getSeaLevelHeight()
        );
        $this->assertEquals(
            $geographicalPosition->getDistanceToEstuaryInKilometers(),
            $gaugingStation->getGeographicalPosition()->getDistanceToEstuaryInKilometers()
        );
        $this->assertEquals(
            $geographicalPosition->getDistanceFromSourceInKilometers(),
            $gaugingStation->getGeographicalPosition()->getDistanceFromSourceInKilometers()
        );

        $this->assertTrue($geographicalPosition->getCoordinates()->equals($gaugingStation->getGeographicalPosition()->getCoordinates()));
    }
}
