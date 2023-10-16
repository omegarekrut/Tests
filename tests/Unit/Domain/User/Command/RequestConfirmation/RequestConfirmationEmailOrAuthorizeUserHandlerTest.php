<?php

namespace Tests\Unit\Domain\User\Command\RequestConfirmation;

use App\Auth\Command\AuthorizeTrustedUserCommand;
use App\Domain\User\Command\RequestConfirmation\Handler\RequestConfirmationEmailOrAuthorizeUserHandler;
use App\Domain\User\Command\RequestConfirmation\RequestConfirmationEmailOrAuthorizeUserCommand;
use App\Domain\User\Command\SendConfirmationEmail\SendConfirmationEmailCommand;
use App\Domain\User\Entity\User;
use App\Domain\User\Exception\EmailConfirmationException;
use App\Domain\User\Repository\UserRepository;
use Tests\Unit\Mock\CommandBusMock;
use Tests\Unit\TestCase;

class RequestConfirmationEmailOrAuthorizeUserHandlerTest extends TestCase
{
    /**
     * @var CommandBusMock
     */
    private $commandBus;

    protected function setUp(): void
    {
        parent::setUp();

        $this->commandBus = new CommandBusMock();
    }

    protected function tearDown(): void
    {
        unset ($this->commandBus);

        parent::tearDown();
    }

    public function testIsConfirmationEmailSent(): void
    {
        $handler = new RequestConfirmationEmailOrAuthorizeUserHandler($this->createUserRepository(), $this->commandBus);
        $command = new RequestConfirmationEmailOrAuthorizeUserCommand();
        $command->login = 'user123';
        $command->password = 'password';
        $handler->handle($command);

        $this->assertInstanceOf(SendConfirmationEmailCommand::class, $this->commandBus->getLastHandledCommand());
    }

    public function testEmailAlreadyConfirmed(): void
    {
        $this->expectException(EmailConfirmationException::class);

        $handler = new RequestConfirmationEmailOrAuthorizeUserHandler($this->createUserRepositoryWhoReturnsAlreadyConfirmedUser(), $this->commandBus);
        $command = new RequestConfirmationEmailOrAuthorizeUserCommand();
        $command->login = 'user123';
        $command->password = 'password';
        $handler->handle($command);

        $this->assertInstanceOf(AuthorizeTrustedUserCommand::class, $this->commandBus->getLastHandledCommand());
    }

    private function createUser(bool $hasConfirmedEmail = false): User
    {
        $user = $this->generateUser();

        if ($hasConfirmedEmail) {
            $user->confirmEmail();
        }

        return $user;
    }

    private function createUserRepositoryWhoReturnsAlreadyConfirmedUser(): UserRepository
    {
        return $this->createUserRepository(true);
    }

    private function createUserRepository(bool $userMayHasConfirmedEmail = false): UserRepository
    {
        $stub = $this->createMock(UserRepository::class);
        $stub
            ->expects($this->once())
            ->method('findOneByLoginOrEmail')
            ->willReturn($this->createUser($userMayHasConfirmedEmail));

        return $stub;
    }
}
