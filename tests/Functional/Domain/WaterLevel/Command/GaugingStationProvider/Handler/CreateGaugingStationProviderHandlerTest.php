<?php

namespace Tests\Functional\Domain\WaterLevel\Command\GaugingStationProvider\Handler;

use App\Domain\WaterLevel\Command\GaugingStationProvider\CreateGaugingStationProviderCommand;
use App\Domain\WaterLevel\Entity\GaugingStationProvider;
use App\Domain\WaterLevel\Entity\ValueObject\ExternalIdentifier;
use App\Domain\WaterLevel\Entity\ValueObject\GaugingStationRecordsProviderKey;
use App\Domain\WaterLevel\Entity\ValueObject\GeographicalPosition;
use App\Domain\WaterLevel\Repository\GaugingStationProviderRepository;
use App\Util\Coordinates\Coordinates;
use Ramsey\Uuid\Uuid;
use Tests\Functional\TestCase;

/**
 * @group water-level
 */
class CreateGaugingStationProviderHandlerTest extends TestCase
{
    public function testHandle(): void
    {
        $geographicalPosition = new GeographicalPosition(new Coordinates(80.4, 45.5), 100, 200, 4);

        $command = new CreateGaugingStationProviderCommand(
            Uuid::uuid4(),
            'Water name',
            new ExternalIdentifier(GaugingStationRecordsProviderKey::meteoNso(), '123', 'Station Name'),
            $geographicalPosition
        );

        $this->getCommandBus()->handle($command);

        $gaugingStationProviderRepository = $this->getContainer()->get(GaugingStationProviderRepository::class);

        $gaugingStationRepository = $gaugingStationProviderRepository->findById($command->id);
        assert($gaugingStationRepository instanceof GaugingStationProvider);

        $this->assertEquals($command->id, $gaugingStationRepository->getId());
        $this->assertEquals($command->waterName, $gaugingStationRepository->getWaterName());
        $this->assertTrue($command->externalIdentifier->equals($gaugingStationRepository->getExternalIdentifier()));

        $this->assertEquals(
            $geographicalPosition->getSeaLevelHeight(),
            $gaugingStationRepository->getGeographicalPosition()->getSeaLevelHeight()
        );
        $this->assertEquals(
            $geographicalPosition->getDistanceToEstuaryInKilometers(),
            $gaugingStationRepository->getGeographicalPosition()->getDistanceToEstuaryInKilometers()
        );
        $this->assertEquals(
            $geographicalPosition->getDistanceFromSourceInKilometers(),
            $gaugingStationRepository->getGeographicalPosition()->getDistanceFromSourceInKilometers()
        );

        $this->assertTrue($geographicalPosition->getCoordinates()->equals($gaugingStationRepository->getGeographicalPosition()->getCoordinates()));
    }
}
