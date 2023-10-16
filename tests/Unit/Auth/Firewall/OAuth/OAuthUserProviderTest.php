<?php

namespace Tests\Unit\Auth\Firewall\OAuth;

use App\Auth\Firewall\OAuth\OAuthUserProvider;
use App\Domain\User\Command\LinkedAccount\AttachLinkedAccountCommand;
use App\Domain\User\Entity\User;
use App\Domain\User\Repository\UserRepository;
use App\Module\OAuth\Entity\OAuthUserInformation;
use App\Module\OAuth\Factory\HWIOAuthUserInformationFactory;
use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use League\Tactician\CommandBus;
use Tests\Unit\TestCase;
use Laminas\Diactoros\Uri;

/**
 * @group oauth
 */
class OAuthUserProviderTest extends TestCase
{
    private const USER_ID = 1;
    private const USER_EMAIL = 'email@email.com';
    private const PROVIDER_USER_UUID = 'uuid';
    private const PROVIDER_NAME = 'providerName';
    private const PROVIDER_USER_PROFILE_URL = 'http://foo.bar';

    /** @var UserResponseInterface */
    private $userResponse;
    /** @var HWIOAuthUserInformationFactory */
    private $userInformationFactory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->userResponse = $this->createMock(UserResponseInterface::class);
        $oauthUserInformation = $this->createOAuthUserInformation(
            self::PROVIDER_USER_UUID,
            self::PROVIDER_NAME,
            self::USER_EMAIL,
            self::PROVIDER_USER_PROFILE_URL
        );
        $this->userInformationFactory = $this->createUserInformationFactory($this->userResponse, $oauthUserInformation);
    }

    public function testLoadUserByOAuthUserResponse(): void
    {
        $expectedUser = $this->createMock(User::class);
        $userProvider = new OAuthUserProvider(
            $this->userInformationFactory,
            $this->createUserRepository($expectedUser),
            $this->createMock(CommandBus::class)
        );
        $actualUser = $userProvider->loadUserByOAuthUserResponse($this->userResponse);

        $this->assertEquals($expectedUser, $actualUser);
    }

    public function testLoadUserByEmailFromResponse(): void
    {
        $expectedUser = $this->createUser(self::USER_ID);
        $userProvider = new OAuthUserProvider(
            $this->userInformationFactory,
            $this->createUserRepository(null, $expectedUser),
            $this->createCommandBus(function ($command) {
                $this->assertInstanceOf(AttachLinkedAccountCommand::class, $command);
                /** @var AttachLinkedAccountCommand $command */
                $this->assertEquals(self::PROVIDER_NAME, $command->providerName);
                $this->assertEquals(self::USER_ID, $command->userId);
                $this->assertEquals(self::PROVIDER_USER_UUID, $command->providerUuid);
                $this->assertEquals(self::PROVIDER_USER_PROFILE_URL, (string) $command->profileUrl);
            })
        );
        $actualUser = $userProvider->loadUserByOAuthUserResponse($this->userResponse);

        $this->assertEquals($expectedUser, $actualUser);
    }

    public function testFailureLoadLoadUser(): void
    {
        $this->expectException(\App\Auth\Firewall\OAuth\Exception\AccountNotLinkedException::class);

        $userProvider = new OAuthUserProvider(
            $this->userInformationFactory,
            $this->createUserRepository(null, null),
            $this->createMock(CommandBus::class)
        );
        $userProvider->loadUserByOAuthUserResponse($this->userResponse);
    }

    private function createUserRepository(?User $foundedUserByProvider = null, ?User $foundedUserByEmail = null): UserRepository
    {
        $stub = $this->createMock(UserRepository::class);
        $stub
            ->expects($this->any())
            ->method('findOneByProvider')
            ->willReturn($foundedUserByProvider);

        $stub
            ->expects($this->any())
            ->method('findOneByLoginOrEmail')
            ->willReturn($foundedUserByEmail);

        return $stub;
    }

    private function createCommandBus(callable $handler): CommandBus
    {
        $stub = $this->createMock(CommandBus::class);
        $stub
            ->expects($this->once())
            ->method('handle')
            ->willReturnCallback($handler);

        return $stub;
    }

    private function createUser(int $id): User
    {
        $stub = $this->createMock(User::class);
        $stub
            ->expects($this->once())
            ->method('getId')
            ->willReturn($id);

        return $stub;
    }

    private function createOAuthUserInformation(string $id, string $provider, string $email, string $profileUrl): OAuthUserInformation
    {
        $stub = $this->createMock(OAuthUserInformation::class);
        $stub
            ->expects($this->any())
            ->method('getId')
            ->willReturn($id);
        $stub
            ->expects($this->any())
            ->method('getProvider')
            ->willReturn($provider);
        $stub
            ->expects($this->any())
            ->method('getEmail')
            ->willReturn($email);
        $stub
            ->expects($this->any())
            ->method('getProfileUrl')
            ->willReturn(new Uri($profileUrl));

        return $stub;
    }

    private function createUserInformationFactory(UserResponseInterface $userResponse, OAuthUserInformation $oauthUserInformation): HWIOAuthUserInformationFactory
    {
        $stub = $this->createMock(HWIOAuthUserInformationFactory::class);
        $stub
            ->expects($this->once())
            ->method('createByUserResponse')
            ->with($userResponse)
            ->willReturn($oauthUserInformation);

        return $stub;
    }
}
