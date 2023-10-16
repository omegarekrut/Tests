<?php

namespace Tests\Functional\Domain\Rating\Command\Handler;

use App\Domain\Rating\Command\DeleteVotableVotesCommand;
use App\Domain\Rating\Command\VoteForRecordCommand;
use App\Domain\Record\Common\Entity\Record;
use App\Domain\User\Entity\User;
use App\Module\Voting\VotableInterface;
use App\Module\Voting\VoteStorage;
use Tests\DataFixtures\ORM\Record\LoadArticles;
use Tests\DataFixtures\ORM\User\LoadUserWithHighRating;
use Tests\Functional\TestCase;

/**
 * @group rating
 */
class DeleteVotableVotesHandlerTest extends TestCase
{
    /** @var VoteStorage */
    private $voteStorage;
    /** @var Record|VotableInterface */
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

        $this->record = $referenceRepository->getReference(LoadArticles::getRandReferenceName());
        $this->user = $referenceRepository->getReference(LoadUserWithHighRating::REFERENCE_NAME);

        $this->voteForRecordAsUser($this->record, $this->user);

        $this->voteStorage = $this->getContainer()->get(VoteStorage::class);
    }

    protected function tearDown(): void
    {
        unset(
            $this->record,
            $this->user,
            $this->voteStorage
        );

        parent::tearDown();
    }

    public function testAfterHandlingStorageShouldNotContainsVoteForVotable(): void
    {
        $command = new DeleteVotableVotesCommand($this->record);
        $this->getCommandBus()->handle($command);

        $actualVotes = $this->voteStorage->getVotes($this->record);

        $this->assertCount(0, $actualVotes);
    }

    public function testAfterHandlingVotableRatingShouldBeRecalculated(): void
    {
        $sourceRating = clone $this->record->getRatingInfo();

        $command = new DeleteVotableVotesCommand($this->record);
        $this->getCommandBus()->handle($command);

        $actualRecord = $this->getEntityManager()->find(Record::class, $this->record->getId());
        $actualRating = $actualRecord->getRatingInfo();

        $this->assertNotEquals($sourceRating, $actualRating);
    }

    private function voteForRecordAsUser(Record $record, User $user): void
    {
        $command = new VoteForRecordCommand($record, 1, $user, '127.0.0.1');

        $this->getCommandBus()->handle($command);
        $this->getEntityManager()->clear();
    }
}
