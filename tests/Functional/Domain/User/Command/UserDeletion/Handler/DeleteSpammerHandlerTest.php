<?php

namespace Tests\Functional\Domain\User\Command\UserDeletion\Handler;

use App\Bridge\Xenforo\ForumApiInterface;
use App\Bridge\Xenforo\Provider\Mock\UserProvider;
use App\Domain\Ban\Service\BanInterface;
use App\Domain\Record\Common\Entity\Record;
use App\Domain\Record\Common\Repository\RecordRepository;
use App\Domain\User\Command\Deleting\DeleteSpammerCommand;
use App\Domain\User\Entity\User;
use Tests\DataFixtures\ORM\User\LoadSpammerUser;
use Tests\DataFixtures\ORM\User\LoadTestUser;
use Tests\DataFixtures\ORM\User\LoadUserWithComments;
use Tests\Functional\TestCase;

/**
 * @group user
 */
class DeleteSpammerHandlerTest extends TestCase
{
    /** @var BanInterface */
    private $banStorage;
    /** @var RecordRepository */
    private $recordRepository;
    /** @var ForumApiInterface */
    private $forumApi;

    protected function setUp(): void
    {
        parent::setUp();

        $this->banStorage = $this->getContainer()->get(BanInterface::class);
        $this->recordRepository = $this->getEntityManager()->getRepository(Record::class);
        $this->forumApi = $this->getContainer()->get(ForumApiInterface::class);
    }

    protected function tearDown(): void
    {
        unset(
            $this->banStorage,
            $this->recordRepository,
            $this->forumApi
        );

        parent::tearDown();
    }

    public function testAfterHandlingSpammerShouldBeBanned(): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadSpammerUser::class,
        ])->getReferenceRepository();

        /** @var User $spammer */
        $spammer = $referenceRepository->getReference(LoadSpammerUser::REFERENCE_NAME);

        $command = new DeleteSpammerCommand();
        $command->spammerId = $spammer->getId();
        $command->cause = 'test removal';

        $this->getCommandBus()->handle($command);

        $banInformation = $this->banStorage->getBanInformationByUserId($spammer->getId());

        $this->assertNotNull($this->banStorage->getBanInformationByUserId($spammer->getId()));
        $this->assertEquals($command->cause, $banInformation->getCause());

        $this->assertTrue($this->banStorage->isBannedByIp($spammer->getLastVisit()->getLastVisitIp()));
    }

    public function testDuringDeletionSpammerShouldLoseContentCreatedByHim(): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadUserWithComments::class,
        ])->getReferenceRepository();

        /** @var User $spammerHavingContent */
        $spammerHavingContent = $referenceRepository->getReference(LoadUserWithComments::REFERENCE_NAME);

        $command = new DeleteSpammerCommand();
        $command->spammerId = $spammerHavingContent->getId();
        $command->cause = 'test removal';

        $this->getCommandBus()->handle($command);

        $this->assertCount(0, $this->recordRepository->findAllCommentedByUser($spammerHavingContent));
    }

    public function testAfterDeletionAlsoSpammerShouldBeDeletedOnForum(): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadTestUser::class,
        ])->getReferenceRepository();

        /** @var User $spammer */
        $spammer = $referenceRepository->getReference(LoadTestUser::USER_TEST);

        $forumUserId = $spammer->getForumUserId();

        $command = new DeleteSpammerCommand();
        $command->spammerId = $spammer->getId();
        $command->cause = 'test removal';

        $this->getCommandBus()->handle($command);

        /** @var UserProvider $userProvider */
        $userProvider = $this->forumApi->user();

        $this->assertTrue($userProvider->isDeletedAsSpammer($forumUserId));
    }
}
