<?php

namespace Tests\Functional\Domain\Rating\Command\Handler;

use App\Domain\Comment\Entity\Comment;
use App\Domain\Rating\Command\VoteForCommentCommand;
use App\Domain\Record\Common\Entity\Record;
use App\Domain\User\Entity\Notification\Notification;
use App\Domain\User\Entity\Notification\PositiveVoteOnCommentNotification;
use App\Domain\User\Entity\User;
use App\Module\Voting\VoteStorage;
use Tests\DataFixtures\ORM\Record\LoadArticles;
use Tests\DataFixtures\ORM\User\LoadTestUser;
use Tests\Functional\TestCase;

/**
 * @group rating
 */
class VoteForCommentHandlerTest extends TestCase
{
    /** @var VoteStorage */
    private $voteStorage;
    /** @var Comment */
    private $comment;
    /** @var User */
    private $user;

    protected function setUp(): void
    {
        parent::setUp();

        $referenceRepository = $this->loadFixtures([
            LoadArticles::class,
            LoadTestUser::class,
        ])->getReferenceRepository();

        /** @var Record $record */
        $record = $referenceRepository->getReference(LoadArticles::getRandReferenceName());
        $this->comment = $record->getComments()->first();
        $this->user = $referenceRepository->getReference(LoadTestUser::USER_TEST);

        $this->voteStorage = $this->getContainer()->get(VoteStorage::class);
    }

    protected function tearDown(): void
    {
        unset(
            $this->comment,
            $this->user,
            $this->voteStorage
        );

        parent::tearDown();
    }

    public function testAfterHandlingStorageShouldContainsVoteFromUser(): void
    {
        $command = new VoteForCommentCommand($this->comment, 1, $this->user, '127.0.0.1');
        $this->getCommandBus()->handle($command);

        $commentVotes = $this->voteStorage->getVotes($this->comment);
        $actualVote = $commentVotes->findByVoter($this->user);

        $this->assertNotEmpty($actualVote);
        $this->assertEquals($command->getVoteValue(), $actualVote->getValue());
        $this->assertEquals($command->getIp(), $actualVote->getVoterIp());
    }

    public function testAfterHandlingCommentRatingMustBeUpdated(): void
    {
        $sourceRatingInfo = $this->comment->getRatingInfo();

        $command = new VoteForCommentCommand($this->comment, 1, $this->user, '127.0.0.1');
        $this->getCommandBus()->handle($command);

        $actualRatingInfo = $this->comment->getRatingInfo();

        $this->assertNotEquals($sourceRatingInfo, $actualRatingInfo);
    }

    /**
     * @group notification
     */
    public function testAfterHandlingRecordAuthorShouldGetNotification(): void
    {
        $command = new VoteForCommentCommand($this->comment, 1, $this->user, '127.0.0.1');
        $this->getCommandBus()->handle($command);

        /** @var Notification|PositiveVoteOnCommentNotification|null $actualNotification */
        $actualNotification = $this->comment->getAuthor()->getUnreadNotifications()->first();

        $this->assertNotEmpty($actualNotification);
        $this->assertInstanceOf(PositiveVoteOnCommentNotification::class, $actualNotification);
    }
}
