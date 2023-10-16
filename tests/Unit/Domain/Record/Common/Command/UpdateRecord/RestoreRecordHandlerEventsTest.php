<?php

namespace Tests\Unit\Domain\Record\Common\Command\UpdateRecord;

use App\Domain\Category\Repository\CategoryRepository;
use App\Domain\Record\Common\Command\UpdateRecord\Handler\RestoreRecordHandler;
use App\Domain\Record\Common\Command\UpdateRecord\RestoreRecordCommand;
use App\Domain\Record\Common\Entity\Record;
use App\Domain\Record\Common\Event\RecordRestoreEvent;
use App\Domain\Record\Common\Repository\RecordRepository;
use Tests\Unit\Mock\EventDispatcherMock;
use Tests\Unit\TestCase;

/**
 * @group record
 */
class RestoreRecordHandlerEventsTest extends TestCase
{
    /** @var EventDispatcherMock */
    private $eventDispatcher;
    /** @var RestoreRecordCommand */
    private $restoreRecordCommand;
    /** @var RestoreRecordHandler */
    private $restoreRecordHandler;
    /** @var Record */
    private $record;

    protected function setUp(): void
    {
        parent::setUp();

        $this->record = $this->createMock(Record::class);
        $recordRepository = $this->createMock(RecordRepository::class);
        $categoryRepository = $this->createMock(CategoryRepository::class);

        $this->eventDispatcher = new EventDispatcherMock();
        $this->restoreRecordCommand = new RestoreRecordCommand($this->record);
        $this->restoreRecordHandler = new RestoreRecordHandler(
            $recordRepository,
            $categoryRepository,
            $this->eventDispatcher
        );
    }

    public function testEventMustBeThrowsAfterSuccessHandling(): void
    {
        $this->restoreRecordHandler->handle($this->restoreRecordCommand);

        $dispatchedEvents = $this->eventDispatcher->getDispatchedEvents();
        /** @var RecordRestoreEvent $lastDispatchedEvent */
        $lastDispatchedEvent = $dispatchedEvents[RecordRestoreEvent::class][0] ?? null;

        $this->assertNotEmpty($lastDispatchedEvent);
        $this->assertInstanceOf(RecordRestoreEvent::class, $lastDispatchedEvent);
        $this->assertEquals($this->record, $lastDispatchedEvent->getRecord());
    }
}
