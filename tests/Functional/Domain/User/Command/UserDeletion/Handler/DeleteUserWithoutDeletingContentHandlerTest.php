<?php

namespace Tests\Functional\Domain\User\Command\UserDeletion\Handler;

use App\Domain\Record\Common\Entity\Record;
use App\Domain\Record\Common\Repository\RecordRepository;
use App\Domain\User\Command\Deleting\DeleteUserWithoutDeletingContentCommand;
use App\Domain\User\Entity\LinkedAccount;
use App\Domain\User\Entity\User;
use App\Domain\User\Repository\UserRepository;
use Tests\DataFixtures\ORM\User\LoadTestUser;
use Tests\DataFixtures\ORM\User\LoadUsersWithLinkedAccount;
use Tests\Functional\TestCase;

class DeleteUserWithoutDeletingContentHandlerTest extends TestCase
{
    /** @var UserRepository */
    private $userRepository;

    /** @var RecordRepository */
    private $recordRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->userRepository = $this->getEntityManager()->getRepository(User::class);
        $this->recordRepository = $this->getEntityManager()->getRepository(Record::class);
    }

    protected function tearDown(): void
    {
        unset(
            $this->user,
            $this->userRepository
        );

        parent::tearDown();
    }

    public function testDeletedUserShouldNotExistInRepository(): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadTestUser::class,
        ])->getReferenceRepository();

        /** @var User $user */
        $user = $referenceRepository->getReference(LoadTestUser::USER_TEST);

        $userId = $user->getId();

        $command = new DeleteUserWithoutDeletingContentCommand();
        $command->userId = $user->getId();

        $this->getCommandBus()->handle($command);

        $actualUser = $this->userRepository->findById($userId);

        $this->assertNull($actualUser);
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

        $command = new DeleteUserWithoutDeletingContentCommand();
        $command->userId = $user->getId();

        $this->getCommandBus()->handle($command);

        $entityManager = $this->getEntityManager();

        foreach ($userLinkedAccountIds as $linkedAccountId) {
            $this->assertNull($entityManager->find(LinkedAccount::class, $linkedAccountId));
        }
    }
}
