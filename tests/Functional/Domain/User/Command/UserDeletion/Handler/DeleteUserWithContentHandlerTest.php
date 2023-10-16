<?php

namespace Tests\Functional\Domain\User\Command\UserDeletion\Handler;

use App\Bridge\Xenforo\ForumApiInterface;
use App\Bridge\Xenforo\Provider\Mock\UserProvider;
use App\Domain\Record\Common\Entity\Record;
use App\Domain\Record\Common\Repository\RecordRepository;
use App\Domain\User\Command\Deleting\DeleteUserWithContentCommand;
use App\Domain\User\Entity\LinkedAccount;
use App\Domain\User\Entity\User;
use App\Domain\User\Repository\UserRepository;
use Tests\DataFixtures\ORM\User\LoadTestUser;
use Tests\DataFixtures\ORM\User\LoadUsersWithLinkedAccount;
use Tests\DataFixtures\ORM\User\LoadUserWithComments;
use Tests\Functional\TestCase;

/**
 * @group user
 */
class DeleteUserWithContentHandlerTest extends TestCase
{
    /** @var User */
    private $user;
    /** @var UserRepository */
    private $userRepository;
    /** @var ForumApiInterface */
    private $forumApi;
    /** @var RecordRepository */
    private $recordRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->userRepository = $this->getEntityManager()->getRepository(User::class);
        $this->forumApi = $this->getContainer()->get(ForumApiInterface::class);
        $this->recordRepository = $this->getEntityManager()->getRepository(Record::class);
    }

    protected function tearDown(): void
    {
        unset(
            $this->user,
            $this->userRepository,
            $this->forumApi
        );

        parent::tearDown();
    }

    public function testDeletedUserShouldBeNotExistsInRepository(): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadTestUser::class,
        ])->getReferenceRepository();

        /** @var User $user */
        $user = $referenceRepository->getReference(LoadTestUser::USER_TEST);

        $userId = $user->getId();

        $command = new DeleteUserWithContentCommand();
        $command->userId = $user->getId();

        $this->getCommandBus()->handle($command);

        $actualUser = $this->userRepository->findById($userId);

        $this->assertNull($actualUser);
    }

    public function testAfterUserDeletionAlsoUserShouldBeDeletedOnForum(): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadTestUser::class,
        ])->getReferenceRepository();

        /** @var User $user */
        $user = $referenceRepository->getReference(LoadTestUser::USER_TEST);

        $forumUserId = $user->getForumUserId();

        $command = new DeleteUserWithContentCommand();
        $command->userId = $user->getId();

        $this->getCommandBus()->handle($command);

        /** @var UserProvider $userProvider */
        $userProvider = $this->forumApi->user();

        $this->assertTrue($userProvider->isHardDeleted($forumUserId));
    }

    public function testDuringDeletionUserShouldLoseContentCreatedByHim(): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadUserWithComments::class,
        ])->getReferenceRepository();

        /** @var User $userHavingContent */
        $userHavingContent = $referenceRepository->getReference(LoadUserWithComments::REFERENCE_NAME);
        $sourceUser = clone $userHavingContent;

        $command = new DeleteUserWithContentCommand();
        $command->userId = $userHavingContent->getId();

        $this->getCommandBus()->handle($command);

        $this->assertCount(0, $this->recordRepository->findAllCommentedByUser($sourceUser));
    }

    public function testAfterHandlingLinkedAccountsAreDeleted(): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadUsersWithLinkedAccount::class,
        ])->getReferenceRepository();

        /** @var User $user */
        $user = $referenceRepository->getReference(LoadUsersWithLinkedAccount::getRandReferenceName());

        $userLinkedAccountIds = $user->getLinkedAccounts()->map(function (LinkedAccount $linkedAccount) {
            return $linkedAccount->getId();
        });

        $command = new DeleteUserWithContentCommand();
        $command->userId = $user->getId();

        $this->getCommandBus()->handle($command);

        $entityManager = $this->getEntityManager();

        foreach ($userLinkedAccountIds as $linkedAccountId) {
            $this->assertNull($entityManager->find(LinkedAccount::class, $linkedAccountId));
        }
    }
}
