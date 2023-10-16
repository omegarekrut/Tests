<?php

namespace Tests\Functional\Domain\User\Command\UserDeletion\Handler;

use App\Domain\User\Command\Deleting\MassDeleteUsersCommand;
use App\Domain\User\Entity\User;
use App\Domain\User\Repository\UserRepository;
use Tests\DataFixtures\ORM\User\LoadModeratorUser;
use Tests\DataFixtures\ORM\User\LoadTestUser;
use Tests\Functional\TestCase;

class MassDeleteUsersHandlerTest extends TestCase
{
    public function testUsersMustBeDeleted(): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadTestUser::class,
            LoadModeratorUser::class,
        ])->getReferenceRepository();

        /** @var UserRepository $userRepository */
        $userRepository = $this->getEntityManager()->getRepository(User::class);
        /** @var User $user */
        $user = $referenceRepository->getReference(LoadTestUser::USER_TEST);
        /** @var User $moderator */
        $moderator = $referenceRepository->getReference(LoadModeratorUser::REFERENCE_NAME);

        $userId = $user->getId();
        $moderatorId = $moderator->getId();

        $userIds = [
            $userId,
            $moderatorId
        ];

        $massDeleteUsersCommand = new MassDeleteUsersCommand();
        $massDeleteUsersCommand->userIds = $userIds;

        $this->getCommandBus()->handle($massDeleteUsersCommand);

        $this->assertNull($userRepository->findById($userId));
        $this->assertNull($userRepository->findById($moderatorId));
    }
}
