<?php

namespace Tests\Functional\Domain\WaterLevel\Command\Handler;

use App\Domain\WaterLevel\Command\CreateGaugingStationRecordCommand;
use App\Domain\WaterLevel\Entity\GaugingStationProvider;
use App\Domain\WaterLevel\Entity\GaugingStationProviderRecord;
use DateTime;
use Ramsey\Uuid\Uuid;
use Tests\DataFixtures\ORM\WaterLevel\LoadEsimoGaugingStationProviderForNovosibirskGaugingStation;
use Tests\Functional\TestCase;

/**
 * @group water-level
 */
class CreateGaugingStationRecordHandlerTest extends TestCase
{
    public function testHandle(): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadEsimoGaugingStationProviderForNovosibirskGaugingStation::class,
        ])->getReferenceRepository();

        $gaugingStationProvider = $referenceRepository->getReference(LoadEsimoGaugingStationProviderForNovosibirskGaugingStation::REFERENCE_NAME);
        assert($gaugingStationProvider instanceof GaugingStationProvider);

        $command = new CreateGaugingStationRecordCommand(Uuid::uuid4(), $gaugingStationProvider, new DateTime(), 50, 14);

        $this->getCommandBus()->handle($command);

        $recordRepository = $this->getEntityManager()->getRepository(GaugingStationProviderRecord::class);

        $gaugingStationRecord = $recordRepository->find($command->id);
        assert($gaugingStationRecord instanceof GaugingStationProviderRecord);

        $this->assertEquals($command->id, $gaugingStationRecord->getId());

        $this->assertEquals($command->gaugingStationProvider, $gaugingStationRecord->getGaugingStationProvider());
        $this->assertEquals($command->recordedAt, $gaugingStationRecord->getRecordedAt());
        $this->assertEquals($command->waterLevel, $gaugingStationRecord->getWaterLevel());
        $this->assertEquals($command->temperature, $gaugingStationRecord->getTemperature());
    }
}
