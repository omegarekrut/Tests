<?php

namespace Tests\Unit\Domain\WaterLevel\Command\Handler;

use App\Domain\WaterLevel\Command\CreateGaugingStationRecordCommand;
use App\Domain\WaterLevel\Command\Handler\CreateGaugingStationRecordHandler;
use App\Domain\WaterLevel\Entity\GaugingStation;
use App\Domain\WaterLevel\Entity\GaugingStationProvider;
use App\Domain\WaterLevel\Event\GaugingStationUpdatedEvent;
use App\Domain\WaterLevel\Repository\GaugingStationRecordRepository;
use DateTime;
use Ramsey\Uuid\Uuid;
use Tests\Unit\Mock\EventDispatcherMock;
use Tests\Unit\TestCase;

class CreateGaugingStationRecordHandlerTest extends TestCase
{
    public function testUpdateFirstAndLastRecordInTheGaugingStationCommandWillCalled(): void
    {
        $eventDispatcher = new EventDispatcherMock();
        $gaugingStationProvider = $this->createMock(GaugingStationProvider::class);
        $gaugingStationProvider->method('getGaugingStation')
            ->willReturn($this->createMock(GaugingStation::class));

        $command = new CreateGaugingStationRecordCommand(
            Uuid::uuid4(),
            $gaugingStationProvider,
            new DateTime(),
            50,
            14,
        );

        $handle = new CreateGaugingStationRecordHandler($this->createMock(GaugingStationRecordRepository::class), $eventDispatcher);
        $handle->handle($command);
        $dispatchedEvents = $eventDispatcher->getDispatchedEvents();

        $lastDispatchedEvent = $dispatchedEvents[GaugingStationUpdatedEvent::class][0] ?? null;

        $this->assertNotEmpty($lastDispatchedEvent);
        $this->assertInstanceOf(GaugingStationUpdatedEvent::class, $lastDispatchedEvent);
    }
}
