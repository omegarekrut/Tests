<?php

namespace Tests\Functional\Domain\User\Command\Notification\Handler;

use App\Domain\Comment\Entity\Comment;
use App\Domain\Record\Common\Entity\Record;
use App\Domain\User\Command\Notification\NotifyVotableAuthorAboutPositiveVoteIfPossibleCommand;
use App\Domain\User\Entity\Notification\Notification;
use App\Domain\User\Entity\Notification\PositiveVoteOnCommentNotification;
use App\Domain\User\Entity\Notification\PositiveVoteOnRecordNotification;
use App\Domain\User\Entity\User;
use App\Module\Voting\Entity\Vote;
use App\Module\Voting\VotableInterface;
use App\Module\Voting\VoteStorage;
use Tests\DataFixtures\ORM\Company\Company\LoadAquaMotorcycleShopsCompany;
use Tests\DataFixtures\ORM\Record\CompanyArticle\LoadAquaMotorcycleShopsCompanyArticle;
use Tests\DataFixtures\ORM\Record\LoadArticles;
use Tests\DataFixtures\ORM\User\LoadUserWhoVotedForRecord;
use Tests\DataFixtures\ORM\User\LoadUserWithAvatar;
use Tests\Functional\TestCase;

/**
 * @group notification
 */
class NotifyAboutPositiveVoteIfPossibleHandlerTest extends TestCase
{
    private VoteStorage $voteStorage;
    private Record $record;
    private Comment $comment;
    private User $user;
    private User $companyEmployee;
    private Record $companyArticle;

    protected function setUp(): void
    {
        parent::setUp();

        $referenceRepository = $this->loadFixtures([
            LoadArticles::class,
            LoadUserWhoVotedForRecord::class,
            LoadAquaMotorcycleShopsCompanyArticle::class,
            LoadAquaMotorcycleShopsCompany::class,
            LoadUserWithAvatar::class,
        ])->getReferenceRepository();

        $this->record = $referenceRepository->getReference(LoadArticles::getRandReferenceName());
        $this->comment = $this->record->getComments()->first();
        $this->user = $referenceRepository->getReference(LoadUserWhoVotedForRecord::REFERENCE_NAME);
        $this->companyArticle = $referenceRepository->getReference(LoadAquaMotorcycleShopsCompanyArticle::REFERENCE_NAME);
        $this->companyEmployee = $referenceRepository->getReference(LoadUserWithAvatar::REFERENCE_NAME);
        $company = $referenceRepository->getReference(LoadAquaMotorcycleShopsCompany::REFERENCE_NAME);

        $company->addEmployee($this->companyEmployee);
        $this->voteStorage = $this->getContainer()->get(VoteStorage::class);
    }

    protected function tearDown(): void
    {
        unset(
            $this->record,
            $this->comment,
            $this->user,
            $this->voteStorage,
            $this->companyArticle,
            $this->companyEmployee
        );

        parent::tearDown();
    }

    public function testAfterRecordHandlingRecordAuthorShouldGetNotification(): void
    {
        $vote = $this->voteStorage->addVote(1, $this->user, $this->record, '127.0.0.1');

        $command = new NotifyVotableAuthorAboutPositiveVoteIfPossibleCommand($this->record, $vote);
        $this->getCommandBus()->handle($command);

        /** @var Notification|PositiveVoteOnRecordNotification|null $actualNotification */
        $actualNotification = $this->record->getAuthor()->getUnreadNotifications()->first();

        $this->assertNotEmpty($actualNotification);
        $this->assertInstanceOf(PositiveVoteOnRecordNotification::class, $actualNotification);
        $this->assertTrue($vote === $actualNotification->getVote());
        $this->assertTrue($this->record === $actualNotification->getOwnerRecord());
    }

    public function testAfterCompanyArticleHandlingCompanyEmployeesShouldGetNotification(): void
    {
        $vote = $this->voteStorage->addVote(1, $this->user, $this->companyArticle, '127.0.0.1');

        $command = new NotifyVotableAuthorAboutPositiveVoteIfPossibleCommand($this->companyArticle, $vote);
        $this->getCommandBus()->handle($command);

        /** @var Notification|PositiveVoteOnRecordNotification|null $actualNotification */
        $actualNotification = $this->companyEmployee->getUnreadNotifications()->first();

        $this->assertNotEmpty($actualNotification);
        $this->assertInstanceOf(PositiveVoteOnRecordNotification::class, $actualNotification);
        $this->assertTrue($vote === $actualNotification->getVote());
        $this->assertTrue($this->companyArticle === $actualNotification->getOwnerRecord());
    }

    public function testAfterCommentHandlingCommentAuthorShouldGetNotification(): void
    {
        $vote = $this->voteStorage->addVote(1, $this->user, $this->comment, '127.0.0.1');

        $command = new NotifyVotableAuthorAboutPositiveVoteIfPossibleCommand($this->comment, $vote);
        $this->getCommandBus()->handle($command);

        /** @var Notification|PositiveVoteOnCommentNotification|null $actualNotification */
        $actualNotification = $this->comment->getAuthor()->getUnreadNotifications()->first();

        $this->assertNotEmpty($actualNotification);
        $this->assertInstanceOf(PositiveVoteOnCommentNotification::class, $actualNotification);
        $this->assertTrue($vote === $actualNotification->getVote());
        $this->assertTrue($this->comment === $actualNotification->getOwnerComment());
    }

    public function testHandlingAnyOtherVotableShouldBeSkipped(): void
    {
        $actualException = null;

        $command = new NotifyVotableAuthorAboutPositiveVoteIfPossibleCommand(
            $this->createMock(VotableInterface::class),
            $this->createMock(Vote::class)
        );

        try {
            $this->getCommandBus()->handle($command);
        } catch (\Throwable $exception) {
            $actualException = $exception;
        }

        $this->assertEmpty($actualException);
    }

    public function testNotificationAboutNegativeVoteShouldBeSkipped(): void
    {
        $vote = $this->voteStorage->addVote(-1, $this->user, $this->record, '127.0.0.1');

        $command = new NotifyVotableAuthorAboutPositiveVoteIfPossibleCommand($this->record, $vote);
        $this->getCommandBus()->handle($command);

        $this->assertCount(0, $this->record->getAuthor()->getUnreadNotifications());
    }

    public function testNotificationAboutVoteSelfRecordShouldBeSkipped(): void
    {
        $vote = $this->voteStorage->addVote(1, $this->record->getAuthor(), $this->record, '127.0.0.1');

        $command = new NotifyVotableAuthorAboutPositiveVoteIfPossibleCommand($this->record, $vote);
        $this->getCommandBus()->handle($command);

        $this->assertCount(0, $this->record->getAuthor()->getUnreadNotifications());
    }
}
