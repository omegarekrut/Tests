<?php

namespace Tests\Unit\Domain\User\Command\UserDeletion\Handler;

use App\Domain\Comment\Collection\CommentCollection;
use App\Domain\Comment\Command\AnonymizeCommentAuthorCommand;
use App\Domain\Comment\Entity\Comment;
use App\Domain\Record\Common\Command\UpdateRecord\AnonymizeRecordAuthorCommand;
use App\Domain\Record\Common\Entity\Record;
use App\Domain\Record\Common\Repository\RecordRepository;
use App\Domain\SuggestedNews\Command\AnonymizeSuggestedNewsAuthorCommand;
use App\Domain\SuggestedNews\Entity\SuggestedNews;
use App\Domain\SuggestedNews\Repository\SuggestedNewsRepository;
use App\Domain\User\Command\Deleting\AnonymizeAllUserCreatedContentCommand;
use App\Domain\User\Command\Deleting\Handler\AnonymizeAllUserCreatedContentHandler;
use App\Domain\User\Entity\User;
use App\Domain\User\Repository\UserRepository;
use App\Module\Voting\VoteStorage;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Tests\Unit\Mock\CommandBusMock;
use Tests\Unit\TestCase;

class AnonymizeAllUserCreatedContentHandlerTest extends TestCase
{
    public function testAnonymizeUserRecords(): void
    {
        $recordIds = [1, 2];
        $records = array_map([$this, 'createRecord'], $recordIds);

        $commandBus = new CommandBusMock();

        $handler = new AnonymizeAllUserCreatedContentHandler(
            $commandBus,
            $this->createRecordRepository($records),
            $this->createMock(UserRepository::class),
            $this->createMock(VoteStorage::class),
            $this->createMock(SuggestedNewsRepository::class)
        );

        $handler->handle(new AnonymizeAllUserCreatedContentCommand($this->createUser()));

        $calledCommands = $commandBus->getAllHandledCommandsOfClass(AnonymizeRecordAuthorCommand::class);

        $this->assertCount(2, $calledCommands);

        foreach ($calledCommands as $calledCommand) {
            $this->assertNotFalse(array_search($calledCommand->getRecord()->getId(), $recordIds));
        }
    }

    public function testAnonymizeUserComments(): void
    {
        $recordIds = [1, 2];
        $recordCommentCounts = [5, 6];
        $records = array_map([$this, 'createRecord'], $recordIds, $recordCommentCounts);

        $commandBus = new CommandBusMock();

        $handler = new AnonymizeAllUserCreatedContentHandler(
            $commandBus,
            $this->createRecordRepository($records),
            $this->createMock(UserRepository::class),
            $this->createMock(VoteStorage::class),
            $this->createMock(SuggestedNewsRepository::class)
        );

        $handler->handle(new AnonymizeAllUserCreatedContentCommand($this->createUser()));

        $calledCommands = $commandBus->getAllHandledCommandsOfClass(AnonymizeCommentAuthorCommand::class);

        $this->assertCount(11, $calledCommands);
    }

    public function testAnonymizeUserSuggestedNews(): void
    {
        $suggestedNewsIds = [Uuid::uuid4(), Uuid::uuid4(), Uuid::uuid4()];
        $suggestedNews = array_map([$this, 'createSuggestedNews'], $suggestedNewsIds);

        $commandBus = new CommandBusMock();

        $handler = new AnonymizeAllUserCreatedContentHandler(
            $commandBus,
            $this->createMock(RecordRepository::class),
            $this->createMock(UserRepository::class),
            $this->createMock(VoteStorage::class),
            $this->createSuggestedNewsRepository($suggestedNews)
        );

        $handler->handle(new AnonymizeAllUserCreatedContentCommand($this->createUser()));

        $calledCommands = $commandBus->getAllHandledCommandsOfClass(AnonymizeSuggestedNewsAuthorCommand::class);

        $this->assertCount(3, $calledCommands);
    }

    private function createRecordRepository(array $records): RecordRepository
    {
        $recordRepository = $this->createMock(RecordRepository::class);
        $recordRepository
            ->method('findAllOwnedByUser')
            ->willReturn($records);
        $recordRepository
            ->method('findAllCommentedByUser')
            ->willReturn($records);

        return $recordRepository;
    }

    private function createSuggestedNewsRepository(array $suggestedNews): SuggestedNewsRepository
    {
        $suggestedNewsRepository = $this->createMock(SuggestedNewsRepository::class);
        $suggestedNewsRepository
            ->method('getAllByAuthor')
            ->willReturn($suggestedNews);

        return $suggestedNewsRepository;
    }

    private function createRecord(int $recordId, int $commentCount = 0): Record
    {
        $record = $this->createMock(Record::class);
        $record
            ->method('getId')
            ->willReturn($recordId);

        $record
            ->method('getComments')
            ->willReturn($this->createCommentCollection($commentCount));
        $record
            ->method('getCommentsWithAnswers')
            ->willReturn(new CommentCollection());

        return $record;
    }

    private function createCommentCollection(int $commentCount): CommentCollection
    {
        $comments = [];

        for ($i = 0; $i < $commentCount; $i++) {
            $comments[] = $this->createMock(Comment::class);
        }

        $commentCollection = $this->createMock(CommentCollection::class);
        $commentCollection
            ->method('findAllByAuthor')
            ->willReturn(new CommentCollection($comments));

        return $commentCollection;
    }

    private function createUser(): User
    {
        return $this->createMock(User::class);
    }

    private function createSuggestedNews(UuidInterface $suggestedNewsId): SuggestedNews
    {
        $suggestedNews = $this->createMock(SuggestedNews::class);
        $suggestedNews
            ->method('getId')
            ->willReturn($suggestedNewsId);

        return $suggestedNews;
    }
}
