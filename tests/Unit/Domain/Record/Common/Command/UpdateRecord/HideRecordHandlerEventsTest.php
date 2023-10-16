<?php

namespace Tests\Unit\Domain\Record\Common\Command\UpdateRecord;

use App\Domain\Category\Repository\CategoryRepository;
use App\Domain\Record\Common\Command\UpdateRecord\Handler\HideRecordHandler;
use App\Domain\Record\Common\Command\UpdateRecord\HideRecordCommand;
use App\Domain\Record\Common\Entity\Record;
use App\Domain\Record\Common\Event\RecordHideEvent;
use App\Domain\Record\Common\Repository\RecordRepository;
use Tests\Unit\Mock\EventDispatcherMock;
use Tests\Unit\TestCase;

/**
 * @group record
 */
class HideRecordHandlerEventsTest extends TestCase
{
    /** @var EventDispatcherMock */
    private $eventDispatcher;
    /** @var HideRecordCommand */
    private $hideRecordCommand;
    /** @var HideRecordHandler */
    private $hideRecordHandler;
    /** @var Record */
    private $record;

    protected function setUp(): void
    {
        parent::setUp();

        $this->record = $this->createMock(Record::class);
        $recordRepository = $this->createMock(RecordRepository::class);
        $categoryRepository = $this->createMock(CategoryRepository::class);

        $this->eventDispatcher = new EventDispatcherMock();
        $this->hideRecordCommand = new HideRecordCommand($this->record);
        $this->hideRecordHandler = new HideRecordHandler(
            $recordRepository,
            $categoryRepository,
            $this->eventDispatcher
        );
    }

    public function testEventMustBeThrowsAfterSuccessHandling(): void
    {
        $this->hideRecordHandler->handle($this->hideRecordCommand);

        $dispatchedEvents = $this->eventDispatcher->getDispatchedEvents();
        /** @var RecordHideEvent $lastDispatchedEvent */
        $lastDispatchedEvent = $dispatchedEvents[RecordHideEvent::class][0] ?? null;

        $this->assertNotEmpty($lastDispatchedEvent);
        $this->assertInstanceOf(RecordHideEvent::class, $lastDispatchedEvent);
        $this->assertEquals($this->record, $lastDispatchedEvent->getRecord());
    }
}
