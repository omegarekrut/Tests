<?php

namespace Tests\Unit\Domain\Record\Common\Command\UpdateRecord;

use App\Domain\Record\Common\Command\UpdateRecord\Handler\UpdateRecordAuthorHandler;
use App\Domain\Record\Common\Command\UpdateRecord\UpdateRecordAuthorCommand;
use App\Domain\Record\Common\Entity\Record;
use App\Domain\Record\Common\Event\RecordAuthorChangedEvent;
use App\Domain\Record\Common\Repository\RecordRepository;
use App\Domain\User\Entity\User;
use App\Module\Author\AuthorFactory;
use App\Module\Author\AuthorInterface;
use Tests\Unit\Mock\EventDispatcherMock;
use Tests\Unit\TestCase;

/**
 * @group record
 */
class UpdateRecordAuthorHandlerTest extends TestCase
{
    private const EXPECTED_OLD_AUTHOR_ID = 42;
    private const EXPECTED_NEW_AUTHOR_ID = 43;

    public function testDispatchedEvent(): void
    {
        $eventDispatcher = new EventDispatcherMock();

        $handler = new UpdateRecordAuthorHandler(
            $this->createMock(RecordRepository::class),
            $this->createAuthorFactory(),
            $eventDispatcher
        );

        $command = new UpdateRecordAuthorCommand($this->createRecord());
        $command->author = 'test';

        $handler->handle($command);

        /** @var RecordAuthorChangedEvent $event */
        $event = $eventDispatcher->findLatestDispatchedEventByName(RecordAuthorChangedEvent::class);

        $this->assertNotEmpty($event);
        $this->assertEquals(self::EXPECTED_OLD_AUTHOR_ID, $event->getOldAuthor()->getId());
        $this->assertEquals(self::EXPECTED_NEW_AUTHOR_ID, $event->getNewAuthor()->getId());
    }

    private function createRecord(): Record
    {
        $stub = $this->createMock(Record::class);

        $stub
            ->method('getAuthor')
            ->willReturn($this->createAuthor(self::EXPECTED_OLD_AUTHOR_ID));

        return $stub;
    }

    private function createAuthorFactory(): AuthorFactory
    {
        $mock = $this->createMock(AuthorFactory::class);

        $mock
            ->method('createFromUsername')
            ->willReturn($this->createAuthor(self::EXPECTED_NEW_AUTHOR_ID));

        return $mock;
    }

    private function createAuthor($id): AuthorInterface
    {
        $author = $this->createMock(User::class);
        $author->method('getId')->willReturn($id);

        return $author;
    }
}
