<?php

namespace Tests\Unit\Domain\User\Command\LinkedAccount;

use App\Domain\User\Command\LinkedAccount\AttachLinkedAccountCommand;
use App\Domain\User\Command\LinkedAccount\Handler\AttachLinkedAccountHandler;
use App\Domain\User\Entity\User;
use App\Domain\User\Entity\LinkedAccount;
use App\Domain\User\Repository\UserRepository;
use Tests\Unit\Mock\ObjectManagerMock;
use Tests\Unit\TestCase;

/**
 * @group auth
 */
class AttachLinkedAccountHandlerTest extends TestCase
{
    public function testAttach(): void
    {
        $command = new AttachLinkedAccountCommand();
        $command->userId = 1;
        $command->providerName = 'provider';
        $command->providerUuid = 'uuid';
        $command->profileUrl = 'http://foo.bar';

        $user = $this->generateUser();
        $objectManager = new ObjectManagerMock();
        $userRepository = $this->createUserRepositoryForFindById($user);

        $handler = new AttachLinkedAccountHandler($userRepository, $objectManager);
        $handler->handle($command);

        $this->assertCount(1, $user->getLinkedAccounts());

        /** @var LinkedAccount $actualLinkedAccount */
        $actualLinkedAccount = $user->getLinkedAccounts()->current();
        $this->assertEquals($command->providerUuid, $actualLinkedAccount->getUuid());
        $this->assertEquals($command->providerName, $actualLinkedAccount->getProviderName());
        $this->assertEquals($command->profileUrl, $actualLinkedAccount->getProfileUrl());
    }

    private function createUserRepositoryForFindById(User $user): UserRepository
    {
        $stub = $this->createMock(UserRepository::class);
        $stub
            ->method('findById')
            ->willReturn($user);

        return $stub;
    }
}
