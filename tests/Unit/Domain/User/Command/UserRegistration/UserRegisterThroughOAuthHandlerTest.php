<?php

namespace Tests\Unit\Domain\User\Command\UserRegistration;

use App\Domain\User\Command\Profile\UpdateBasicInformationCommand;
use App\Domain\User\Command\LinkedAccount\AttachLinkedAccountCommand;
use App\Domain\User\Command\UserRegistration\Handler\UserRegisterThroughOAuthHandler;
use App\Domain\User\Command\UserRegistration\UserRegisterCommand;
use App\Domain\User\Command\UserRegistration\UserRegisterThroughOAuthCommand;
use App\Domain\User\Entity\User;
use App\Domain\User\Entity\ValueObject\City;
use App\Domain\User\Entity\ValueObject\Gender as UserGender;
use App\Domain\User\Generator\PasswordGenerator;
use App\Domain\User\Generator\UsernameByEmailGenerator;
use App\Module\OAuth\Entity\ValueObject\Gender;
use Carbon\Carbon;
use League\Tactician\CommandBus;
use PHPUnit\Framework\MockObject\Stub\ReturnCallback;
use Tests\Unit\TestCase;

/**
 * @group registration
 * @group oauth
 */
class UserRegisterThroughOAuthHandlerTest extends TestCase
{
    private const GENERATED_USERNAME = 'generetad-username';
    private const GENERATED_PASSWORD = 'generetad-password';
    private const USER_ID = 1;
    private const TRUSTED_OAUTH_EMAIL = 'trusted-email@email.com';

    /** @var UserRegisterThroughOAuthCommand */
    private $command;

    protected function setUp(): void
    {
        parent::setUp();

        $this->command = new UserRegisterThroughOAuthCommand(self::TRUSTED_OAUTH_EMAIL);
        $this->command->email = 'email@email.com';
        $this->command->gender = new Gender(Gender::FEMALE);
        $this->command->city = 'city';
        $this->command->dateBirthday = Carbon::now();
        $this->command->name = 'name';
    }

    public function testHandleModifiedCommand(): void
    {
        $user = $this->createUser(self::USER_ID);
        $userRegisterThroughOAuthCommand = $this->command;

        $commandBus = $this->createCommandBus([
            function ($command) use ($userRegisterThroughOAuthCommand, $user) {
                $this->assertInstanceOf(UserRegisterCommand::class, $command);
                /** @var UserRegisterCommand $command */
                $this->assertEquals($userRegisterThroughOAuthCommand->email, $command->email);
                $this->assertEquals(self::GENERATED_USERNAME, $command->username);
                $this->assertEquals(self::GENERATED_PASSWORD, $command->password);
                $this->assertFalse($command->isTrustedRegistration());

                return $user;
            },
            function ($command) use ($userRegisterThroughOAuthCommand) {
                $this->assertInstanceOf(AttachLinkedAccountCommand::class, $command);
                /** @var AttachLinkedAccountCommand $command */
                $this->assertEquals(self::USER_ID, $command->userId);
                $this->assertEquals($userRegisterThroughOAuthCommand->profileUrl, $command->profileUrl);
                $this->assertEquals($userRegisterThroughOAuthCommand->providerName, $command->providerName);
                $this->assertEquals($userRegisterThroughOAuthCommand->providerUuid, $command->providerUuid);
            },
            function ($command) use ($userRegisterThroughOAuthCommand) {
                $this->assertInstanceOf(UpdateBasicInformationCommand::class, $command);
                /** @var UpdateBasicInformationCommand $command */
                $this->assertEquals(self::USER_ID, $command->getUser()->getId());
                $this->assertEquals($userRegisterThroughOAuthCommand->name, $command->name);
                $this->assertEquals($userRegisterThroughOAuthCommand->dateBirthday, $command->birthdate);
                $this->assertEquals($userRegisterThroughOAuthCommand->city, $command->cityName);
                $this->assertEquals((string) UserGender::FEMALE(), $command->gender);
            },
        ]);

        $usernameGenerator = $this->createUsernameGenerator(self::GENERATED_USERNAME, $userRegisterThroughOAuthCommand->email);
        $passwordGenerator = $this->createPasswordGenerator(self::GENERATED_PASSWORD);

        $handler = new UserRegisterThroughOAuthHandler($commandBus, $usernameGenerator, $passwordGenerator);
        $handler->handle($this->command);
    }

    public function testHandleCommandForTrustedEmail(): void
    {
        $user = $this->createUser(self::USER_ID);
        $userRegisterThroughOAuthCommand = $this->command;
        $userRegisterThroughOAuthCommand->email = self::TRUSTED_OAUTH_EMAIL;

        $commandBus = $this->createCommandBus([
            function ($command) use ($userRegisterThroughOAuthCommand, $user) {
                $this->assertInstanceOf(UserRegisterCommand::class, $command);
                /** @var UserRegisterCommand $command */
                $this->assertTrue($command->isTrustedRegistration());

                return $user;
            },
            function ($command) {},
            function ($command) {},
        ]);

        $usernameGenerator = $this->createUsernameGenerator(self::GENERATED_USERNAME, $userRegisterThroughOAuthCommand->email);
        $passwordGenerator = $this->createPasswordGenerator(self::GENERATED_PASSWORD);

        $handler = new UserRegisterThroughOAuthHandler($commandBus, $usernameGenerator, $passwordGenerator);
        $handler->handle($this->command);
    }

    private function createCommandBus(array $handlers): CommandBus
    {
        $stub = $this->createMock(CommandBus::class);

        $returnMap = [];

        foreach ($handlers as $handler) {
            $returnMap[] = new ReturnCallback($handler);
        }

        $stub
            ->method('handle')
            ->willReturnOnConsecutiveCalls(...$returnMap);

        return $stub;
    }

    private function createUser(int $userId): User
    {
        $stub = $this->createMock(User::class);
        $stub
            ->expects($this->any())
            ->method('getId')
            ->willReturn($userId);

        $stub
            ->expects($this->any())
            ->method('getName')
            ->willReturn('');

        $stub
            ->expects($this->any())
            ->method('getGender')
            ->willReturn(null);

        $stub
            ->expects($this->any())
            ->method('getBirthdate')
            ->willReturn(null);

        $stub
            ->expects($this->any())
            ->method('getCity')
            ->willReturn($this->createMock(City::class));

        return $stub;
    }

    private function createUsernameGenerator(string $username, string $expectedEmail): UsernameByEmailGenerator
    {
        $stub = $this->createMock(UsernameByEmailGenerator::class);
        $stub
            ->expects($this->once())
            ->method('generate')
            ->with($expectedEmail)
            ->willReturn($username);

        return $stub;
    }

    private function createPasswordGenerator(string $password): PasswordGenerator
    {
        $stub = $this->createMock(PasswordGenerator::class);
        $stub
            ->expects($this->once())
            ->method('generate')
            ->willReturn($password);

        return $stub;
    }
}
