<?php

namespace Tests\Unit\Domain\User\Entity;

use App\Domain\User\Entity\LinkedAccount;
use App\Domain\User\Entity\User;
use Ramsey\Uuid\Uuid;
use Tests\Unit\TestCase;

class UserLinkedAccountTest extends TestCase
{
    /** @var User */
    private $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = $this->generateUser();
    }

    protected function tearDown(): void
    {
        unset($this->user);

        parent::tearDown();
    }

    public function testUserCanLinkAccount(): void
    {
        $expectedAccountUuid = (string) Uuid::uuid4();
        $expectedProviderName = 'provider';
        $expectedProfileUrl = 'http://provider/profile/url';

        $this->user->attachLinkedAccount($expectedAccountUuid, $expectedProviderName, $expectedProfileUrl);

        $linkedAccounts = $this->user->getLinkedAccounts();

        $this->assertCount(1, $linkedAccounts);

        $actualLinkedAccount = $linkedAccounts->first();
        assert($actualLinkedAccount instanceof LinkedAccount);

        $this->assertEquals($expectedAccountUuid, $actualLinkedAccount->getUuid());
        $this->assertEquals($expectedProviderName, $actualLinkedAccount->getProviderName());
        $this->assertEquals($expectedProfileUrl, $actualLinkedAccount->getProfileUrl());
    }

    public function testUserCanLinkSeveralProvidedAccounts(): void
    {
        $firstProviderName = 'first provider';
        $secondProviderName = 'second provider';

        $this->user->attachLinkedAccount((string) Uuid::uuid4(), $firstProviderName);
        $this->user->attachLinkedAccount((string) Uuid::uuid4(), $secondProviderName);

        $linkedAccounts = $this->user->getLinkedAccounts();

        $this->assertCount(2, $linkedAccounts);

        $this->assertContains($firstProviderName, $linkedAccounts->getProviderNames());
        $this->assertContains($secondProviderName, $linkedAccounts->getProviderNames());
    }

    public function testAlreadyLinkedAccountFromProviderMustBeOverwrittenWithNewAccount(): void
    {
        $providerName = 'provider';

        $this->user->attachLinkedAccount((string) Uuid::uuid4(), $providerName);

        $newAccountUuid = (string) Uuid::uuid4();

        $this->user->attachLinkedAccount($newAccountUuid, $providerName);

        $linkedAccounts = $this->user->getLinkedAccounts();

        $this->assertCount(1, $linkedAccounts);

        $actualLinkedAccount = $linkedAccounts->first();
        assert($actualLinkedAccount instanceof LinkedAccount);

        $this->assertEquals($newAccountUuid, $actualLinkedAccount->getUuid());
    }

    public function testLinkedAccountCanBeDetached(): void
    {
        $this->user->attachLinkedAccount((string) Uuid::uuid4(), 'provider');

        $linkedAccount = $this->user->getLinkedAccounts()->first();
        assert($linkedAccount instanceof LinkedAccount);

        $this->user->detachLinkedAccount($linkedAccount);

        $this->assertCount(0, $this->user->getLinkedAccounts());
    }

    public function testForeignAccountCannotBeDetached(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('trying detach provider for user');

        $otherUser = $this->generateUser();
        $otherUser->attachLinkedAccount((string) Uuid::uuid4(), 'provider');

        $foreignAccount = $otherUser->getLinkedAccounts()->first();
        assert($foreignAccount instanceof LinkedAccount);

        $this->user->detachLinkedAccount($foreignAccount);
    }
}
