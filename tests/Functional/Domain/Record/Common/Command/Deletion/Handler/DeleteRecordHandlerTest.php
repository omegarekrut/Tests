<?php

namespace Tests\Functional\Domain\Record\Common\Command\Deletion\Handler;

use App\Domain\Category\Entity\Category;
use App\Domain\Category\Repository\CategoryRepository;
use App\Domain\Rating\Command\VoteForRecordCommand;
use App\Domain\Record\Common\Command\Deleting\DeleteRecordCommand;
use App\Domain\Record\Common\Entity\Record;
use App\Domain\Record\Common\Repository\RecordRepository;
use App\Domain\User\Entity\User;
use App\Module\Voting\VoteStorage;
use Tests\DataFixtures\ORM\Record\LoadArticles;
use Tests\DataFixtures\ORM\User\LoadUserWithHighRating;
use Tests\Functional\TestCase;

/**
 * @group record
 */
class DeleteRecordHandlerTest extends TestCase
{
    /** @var RecordRepository*/
    private $recordRepository;
    /** @var VoteStorage */
    private $voteStorage;
    /** @var CategoryRepository */
    private $categoryRepository;
    /** @var Record */
    private $record;
    /** @var User */
    private $user;

    protected function setUp(): void
    {
        parent::setUp();

        $referenceRepository = $this->loadFixtures([
            LoadArticles::class,
            LoadUserWithHighRating::class,
        ])->getReferenceRepository();

        $this->user = $referenceRepository->getReference(LoadUserWithHighRating::REFERENCE_NAME);
        $this->record = $referenceRepository->getReference(LoadArticles::getRandReferenceName());

        $this->recordRepository = $this->getEntityManager()->getRepository(Record::class);
        $this->voteStorage = $this->getContainer()->get(VoteStorage::class);
        $this->categoryRepository = $this->getEntityManager()->getRepository(Category::class);
    }

    protected function tearDown(): void
    {
        unset(
            $this->user,
            $this->record,
            $this->deleteRecordCommand,
            $this->recordRepository,
            $this->voteStorage,
            $this->categoryRepository
        );

        parent::tearDown();
    }

    public function testAfterDeletingRecordShouldBeRemovedFromRepository(): void
    {
        $recordId = $this->record->getId();

        $deleteRecordCommand = new DeleteRecordCommand($this->record);
        $this->getCommandBus()->handle($deleteRecordCommand);

        $this->getEntityManager()->clear();

        $actualRecord = $this->recordRepository->findById($recordId);

        $this->assertNull($actualRecord);
    }

    public function testAfterDeletingVotesForRecordMustBeDeleted(): void
    {
        $this->voteForRecordByUser($this->record, $this->user);
        $this->getEntityManager()->clear();

        $votedRecord = $this->recordRepository->findById($this->record->getId());
        $sourceRecord = clone $votedRecord;

        $deleteRecordCommand = new DeleteRecordCommand($votedRecord);
        $this->getCommandBus()->handle($deleteRecordCommand);

        $this->assertCount(0, $this->voteStorage->getVotes($sourceRecord));
    }

    public function testRecordCountInCategoryShouldDecreaseAfterDeleting(): void
    {
        $sourceCategory = $this->record->getCategory();
        $expectedRecordCountInCategory = $sourceCategory->getRecordsCount() - 1;

        $deleteRecordCommand = new DeleteRecordCommand($this->record);
        $this->getCommandBus()->handle($deleteRecordCommand);

        $actualCategory = $this->categoryRepository->findById($sourceCategory->getId());

        $this->assertEquals($expectedRecordCountInCategory, $actualCategory->getRecordsCount());
    }

    private function voteForRecordByUser(Record $record, User $user): void
    {
        $command = new VoteForRecordCommand($record, 1, $user, '127.0.0.1');

        $this->getCommandBus()->handle($command);
    }
}
