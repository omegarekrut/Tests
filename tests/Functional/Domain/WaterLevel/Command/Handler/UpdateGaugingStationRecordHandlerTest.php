<?php

namespace Tests\Functional\Domain\WaterLevel\Command\Handler;

use App\Domain\WaterLevel\Command\UpdateGaugingStationRecordCommand;
use App\Domain\WaterLevel\Entity\GaugingStationProvider;
use App\Domain\WaterLevel\Entity\GaugingStationProviderRecord;
use Tests\DataFixtures\ORM\WaterLevel\LoadEsimoGaugingStationProviderForNovosibirskGaugingStation;
use Tests\Functional\TestCase;

/**
 * @group water-level
 */
class UpdateGaugingStationRecordHandlerTest extends TestCase
{
    public function testHandle(): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadEsimoGaugingStationProviderForNovosibirskGaugingStation::class,
        ])->getReferenceRepository();

        $gaugingStationProvider = $referenceRepository->getReference(LoadEsimoGaugingStationProviderForNovosibirskGaugingStation::REFERENCE_NAME);
        assert($gaugingStationProvider instanceof GaugingStationProvider);

        $record = $gaugingStationProvider->getRecords()->first();
        assert($record instanceof GaugingStationProviderRecord);

        $waterLevel = $record->getWaterLevel() + 1;
        $temperature = (float) $record->getTemperature() + 1;

        $command = new UpdateGaugingStationRecordCommand(
            $record,
            $waterLevel,
            $temperature
        );

        $this->getCommandBus()->handle($command);

        $this->assertEquals($waterLevel, $record->getWaterLevel());
        $this->assertEquals($temperature, $record->getTemperature());
    }
}
