<?php

namespace Tests\Unit\Auth\Firewall\OAuth;

use App\Auth\Firewall\OAuth\OAuthAccountConnector;
use App\Domain\User\Command\LinkedAccount\AttachLinkedAccountCommand;
use App\Domain\User\Entity\User;
use App\Module\OAuth\Entity\OAuthUserInformation;
use App\Module\OAuth\Factory\HWIOAuthUserInformationFactory;
use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use League\Tactician\CommandBus;
use Tests\Unit\TestCase;
use Laminas\Diactoros\Uri;

/**
 * @group oauth
 */
class OAuthAccountConnectorTest extends TestCase
{
    private const USER_ID = 1;
    private const PROVIDER_USER_ID = 'id';
    private const PROVIDER_NAME = 'providerName';
    private const PROVIDER_PROFILE_URL = 'http://foo.bar';

    public function testConnection(): void
    {
        $userResponse = $this->createMock(UserResponseInterface::class);
        $userInformation = $this->createUserInformation(self::PROVIDER_USER_ID, self::PROVIDER_NAME, self::PROVIDER_PROFILE_URL);
        $userInformationFactory = $this->createHWIOAuthUserInformationFactory($userResponse, $userInformation);
        $commandBus = $this->createCommandBus(function ($command) {
            $this->assertInstanceOf(AttachLinkedAccountCommand::class, $command);
            /** @var AttachLinkedAccountCommand $command */
            $this->assertEquals(self::USER_ID, $command->userId);
            $this->assertEquals(self::PROVIDER_USER_ID, $command->providerUuid);
            $this->assertEquals(self::PROVIDER_NAME, $command->providerName);
            $this->assertEquals(self::PROVIDER_PROFILE_URL, (string) $command->profileUrl);
        });
        $user = $this->createUser(self::USER_ID);

        $connector = new OAuthAccountConnector($userInformationFactory, $commandBus);
        $connector->connect($user, $userResponse);
    }

    private function createHWIOAuthUserInformationFactory(UserResponseInterface $userResponse, OAuthUserInformation $userInformation)
    {
        $stub = $this->createMock(HWIOAuthUserInformationFactory::class);
        $stub
            ->method('createByUserResponse')
            ->with($userResponse)
            ->willReturn($userInformation);

        return $stub;
    }

    private function createUserInformation(string $id, string $provider, string $profileUrl): OAuthUserInformation
    {
        $stub = $this->createMock(OAuthUserInformation::class);
        $stub
            ->method('getId')
            ->willReturn($id);
        $stub
            ->method('getProvider')
            ->willReturn($provider);
        $stub
            ->method('getProfileUrl')
            ->willReturn(new Uri($profileUrl));

        return $stub;
    }

    private function createCommandBus(callable $handler): CommandBus
    {
        $stub = $this->createMock(CommandBus::class);
        $stub
            ->method('handle')
            ->willReturnCallback($handler);

        return $stub;
    }

    private function createUser(int $id): User
    {
        $stub = $this->createMock(User::class);
        $stub
            ->method('getId')
            ->willReturn($id);

        return $stub;
    }
}
