<?php

namespace Tests\Unit\Domain\Record\Common\Command\UpdateRecord;

use App\Domain\Record\Common\Command\UpdateRecord\Handler\UpdateRecordPriorityHandler;
use App\Domain\Record\Common\Command\UpdateRecord\UpdateRecordPriorityCommand;
use App\Domain\Record\Common\Entity\Record;
use App\Domain\Record\Common\Repository\RecordRepository;
use Tests\Unit\TestCase;

/**
 * @group record
 */
class UpdateRecordPriorityHandlerTest extends TestCase
{
    public function testHandle(): void
    {
        $handler = new UpdateRecordPriorityHandler($this->getMockRecordRepository());

        $command = new UpdateRecordPriorityCommand($this->getMockRecord());
        $command->priority = 2;

        $handler->handle($command);
    }

    private function getMockRecordRepository(): RecordRepository
    {
        $stub = $this->createMock(RecordRepository::class);
        $stub
            ->expects($this->once())
            ->method('save')
            ->willReturnCallback(function ($entity) {
                $this->assertInstanceOf(Record::class, $entity);
            });

        return $stub;
    }

    private function getMockRecord(): Record
    {
        $stub = $this->createMock(Record::class);

        $stub
            ->expects($this->once())
            ->method('updatePriority')
            ->with(2);

        return $stub;
    }
}
