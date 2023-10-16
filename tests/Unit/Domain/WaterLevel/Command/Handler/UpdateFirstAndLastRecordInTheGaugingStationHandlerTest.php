<?php

namespace Tests\Unit\Domain\WaterLevel\Command\Handler;

use App\Domain\WaterLevel\Command\Handler\UpdateFirstAndLastRecordInTheGaugingStationHandler;
use App\Domain\WaterLevel\Command\UpdateFirstAndLastRecordInTheGaugingStationCommand;
use App\Domain\WaterLevel\Entity\GaugingStation;
use App\Domain\WaterLevel\Entity\GaugingStationProviderRecord;
use App\Domain\WaterLevel\Entity\ValueObject\GeographicalPosition;
use App\Domain\WaterLevel\Entity\Water;
use App\Domain\WaterLevel\Repository\GaugingStationRecordRepository;
use App\Domain\WaterLevel\Repository\GaugingStationRepository;
use Ramsey\Uuid\Uuid;
use Tests\Unit\TestCase;

class UpdateFirstAndLastRecordInTheGaugingStationHandlerTest extends TestCase
{
    public function testHandle(): void
    {
        $gaugingStation = $this->createGaugingStation();
        $firstGaugingStationRecord = $this->createGaugingStationRecordMock();
        $lastGaugingStationRecord = $this->createGaugingStationRecordMock();

        $command = new UpdateFirstAndLastRecordInTheGaugingStationCommand($gaugingStation->getId());

        $handler = new UpdateFirstAndLastRecordInTheGaugingStationHandler(
            $this->createGaugingStationRepositoryMock($gaugingStation),
            $this->createGaugingStationRecordRepositoryMock($firstGaugingStationRecord, $lastGaugingStationRecord)
        );

        $handler->handle($command);

        $this->assertEquals($firstGaugingStationRecord, $gaugingStation->getFirstRecord());
        $this->assertEquals($lastGaugingStationRecord, $gaugingStation->getLatestRecord());
    }

    private function createGaugingStation(): GaugingStation
    {
        return new GaugingStation(
            Uuid::uuid4(),
            'short-uuid',
            'slug',
            $this->createMock(Water::class),
            'name',
            $this->createMock(GeographicalPosition::class),
        );
    }

    private function createGaugingStationRecordMock(): GaugingStationProviderRecord
    {
        return $this->createMock(GaugingStationProviderRecord::class);
    }

    private function createGaugingStationRecordRepositoryMock(GaugingStationProviderRecord $firstRecord, GaugingStationProviderRecord $lastRecord): GaugingStationRecordRepository
    {
        $gaugingStationRecordRepository = $this->createMock(GaugingStationRecordRepository::class);

        $gaugingStationRecordRepository->method('getOneFirstByGaugingStation')->willReturn($firstRecord);
        $gaugingStationRecordRepository->method('getOneLastByGaugingStation')->willReturn($lastRecord);

        return $gaugingStationRecordRepository;
    }

    private function createGaugingStationRepositoryMock(GaugingStation $gaugingStation): GaugingStationRepository
    {
        $gaugingStationRepository = $this->createMock(GaugingStationRepository::class);

        $gaugingStationRepository->method('find')->willReturn($gaugingStation);

        return $gaugingStationRepository;
    }
}
