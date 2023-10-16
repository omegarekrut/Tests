<?php

namespace Tests\Functional\Domain\WaterLevel\Command\Handler;

use App\Domain\WaterLevel\Command\UpdateGaugingStationCommand;
use App\Domain\WaterLevel\Entity\GaugingStation;
use App\Domain\WaterLevel\Entity\GaugingStationProvider;
use Doctrine\Common\Collections\ArrayCollection;
use Tests\DataFixtures\ORM\WaterLevel\LoadGaugingStationProviderWithoutGaugingStation;
use Tests\DataFixtures\ORM\WaterLevel\LoadNovosibirskGaugingStation;
use Tests\Functional\TestCase;

/**
 * @group water-level
 */
class UpdateGaugingStationHandlerTest extends TestCase
{
    public function testGaugingStationIsChanged(): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadGaugingStationProviderWithoutGaugingStation::class,
            LoadNovosibirskGaugingStation::class,
        ])->getReferenceRepository();

        $gaugingStation = $referenceRepository->getReference(LoadNovosibirskGaugingStation::REFERENCE_NAME);
        assert($gaugingStation instanceof GaugingStation);

        $gaugingStationProvider = $referenceRepository->getReference(LoadGaugingStationProviderWithoutGaugingStation::REFERENCE_NAME);
        assert($gaugingStationProvider instanceof GaugingStationProvider);

        $command = new UpdateGaugingStationCommand($gaugingStation);

        $command->name = 'Test Name';
        $command->distanceFromSourceInKilometers = 1000;
        $command->distanceToEstuaryInKilometers = 2000;
        $command->seaLevelHeight = 777;
        $command->gaugingStationProviders = new ArrayCollection([$gaugingStationProvider]);

        $this->getCommandBus()->handle($command);

        $this->assertEquals($command->name, $gaugingStation->getName());
        $this->assertEquals($command->distanceFromSourceInKilometers, $gaugingStation->getDistanceFromSource());
        $this->assertEquals($command->distanceToEstuaryInKilometers, $gaugingStation->getDistanceToEstuary());
        $this->assertEquals($command->seaLevelHeight, $gaugingStation->getSeaLevelHeight());
        $this->assertContains($gaugingStationProvider, $gaugingStation->getGaugingStationProviders());
    }
}
