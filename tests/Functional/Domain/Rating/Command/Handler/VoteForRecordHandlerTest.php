<?php

namespace Tests\Functional\Domain\Rating\Command\Handler;

use App\Domain\Rating\Command\VoteForRecordCommand;
use App\Domain\Record\Common\Entity\Record;
use App\Domain\User\Entity\Notification\Notification;
use App\Domain\User\Entity\Notification\PositiveVoteOnRecordNotification;
use App\Domain\User\Entity\User;
use App\Module\Voting\VoteStorage;
use Tests\DataFixtures\ORM\Record\LoadArticles;
use Tests\DataFixtures\ORM\User\LoadUserWithHighRating;
use Tests\Functional\TestCase;

/**
 * @group rating
 */
class VoteForRecordHandlerTest extends TestCase
{
    /** @var VoteStorage */
    private $voteStorage;
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

        /** @var Record $record */
        $this->record = $referenceRepository->getReference(LoadArticles::getRandReferenceName());
        $this->user = $referenceRepository->getReference(LoadUserWithHighRating::REFERENCE_NAME);

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

    public function testAfterHandlingStorageShouldContainsVoteFromUser(): void
    {
        $command = new VoteForRecordCommand($this->record, 1, $this->user, '127.0.0.1');
        $this->getCommandBus()->handle($command);

        $recordVotes = $this->voteStorage->getVotes($this->record);
        $actualVote = $recordVotes->findByVoter($this->user);

        $this->assertNotEmpty($actualVote);
        $this->assertEquals($command->getVoteValue(), $actualVote->getValue());
        $this->assertEquals($command->getIp(), $actualVote->getVoterIp());
    }

    public function testAfterHandlingRecordRatingMustBeUpdated(): void
    {
        $sourceRatingInfo = $this->record->getRatingInfo();

        $command = new VoteForRecordCommand($this->record, 1, $this->user, '127.0.0.1');
        $this->getCommandBus()->handle($command);

        $actualRatingInfo = $this->record->getRatingInfo();

        $this->assertNotEquals($sourceRatingInfo, $actualRatingInfo);
    }

    /**
     * @group notification
     */
    public function testAfterHandlingRecordAuthorShouldGetNotification(): void
    {
        $command = new VoteForRecordCommand($this->record, 1, $this->user, '127.0.0.1');
        $this->getCommandBus()->handle($command);

        /** @var Notification|PositiveVoteOnRecordNotification|null $actualNotification */
        $actualNotification = $this->record->getAuthor()->getUnreadNotifications()->first();

        $this->assertNotEmpty($actualNotification);
        $this->assertInstanceOf(PositiveVoteOnRecordNotification::class, $actualNotification);
    }
}
