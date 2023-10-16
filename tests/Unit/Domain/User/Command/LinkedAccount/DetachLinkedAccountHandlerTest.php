<?php

namespace Tests\Unit\Domain\User\Command\LinkedAccount;

use App\Domain\User\Command\LinkedAccount\DetachLinkedAccountCommand;
use App\Domain\User\Command\LinkedAccount\Handler\DetachLinkedAccountHandler;
use Tests\Unit\Mock\ObjectManagerMock;
use Tests\Unit\TestCase;

class DetachLinkedAccountHandlerTest extends TestCase
{
    private const EXPECTED_PROVIDER_NAME = 'providerName';
    private const EXPECTED_PROVIDER_UUID = 'uuid';
    private const EXPECTED_PROFILE_URL = 'http://foo.bar';

    public function testUserDeleteHisLinkedAccount(): void
    {
        $user = $this->generateUser();
        $user
            ->attachLinkedAccount(self::EXPECTED_PROVIDER_UUID, self::EXPECTED_PROVIDER_NAME, self::EXPECTED_PROFILE_URL);

        $linkedAccount = $user->getLinkedAccounts()->first();

        $command = new DetachLinkedAccountCommand($linkedAccount, $user);
        $handler = new DetachLinkedAccountHandler(new ObjectManagerMock());
        $handler->handle($command);

        $this->assertNotContains($linkedAccount, $user->getLinkedAccounts());
    }

    public function testUserCantDeleteAnotherLinkedAccount(): void
    {
        $this->expectException(\RuntimeException::class);

        $user = $this->generateUser();
        $user
            ->attachLinkedAccount(self::EXPECTED_PROVIDER_UUID, self::EXPECTED_PROVIDER_NAME, self::EXPECTED_PROFILE_URL);

        $linkedAccount = $user->getLinkedAccounts()->first();

        $anotherUser = $this->generateUser();

        $command = new DetachLinkedAccountCommand($linkedAccount, $anotherUser);
        $handler = new DetachLinkedAccountHandler(new ObjectManagerMock());
        $handler->handle($command);
    }
}
