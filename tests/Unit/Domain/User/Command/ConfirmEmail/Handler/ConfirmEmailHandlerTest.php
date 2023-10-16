<?php

namespace Tests\Unit\Domain\User\Command\ConfirmEmail\Handler;

use App\Auth\Command\AuthorizeTrustedUserCommand;
use App\Domain\User\Command\ConfirmEmail\ConfirmEmailCommand;
use App\Domain\User\Command\ConfirmEmail\Handler\ConfirmEmailHandler;
use App\Domain\User\Entity\User;
use App\Domain\User\Entity\ValueObject\Token;
use App\Domain\User\Repository\UserRepository;
use Tests\Unit\Mock\CommandBusMock;
use Tests\Unit\Mock\ObjectManagerMock;
use Tests\Unit\TestCase;

class ConfirmEmailHandlerTest extends TestCase
{
    private $commandBus;
    private $objectManager;

    protected function setUp(): void
    {
        parent::setUp();

        $this->commandBus = new CommandBusMock();
        $this->objectManager = new ObjectManagerMock();
    }

    public function testConfirmationEmail(): void
    {
        $token = new Token('some-token');
        $command = new ConfirmEmailCommand($token->getToken());
        $user = $this->generateUser();
        $user->getEmail()->setConfirmationToken($token);

        $handler = new ConfirmEmailHandler(
            $this->commandBus,
            $this->objectManager,
            $this->getUserRepository($user)
        );

        $handler->handle($command);

        $this->assertTrue($user->getEmail()->isConfirmed());
        $this->assertNotEquals($token, $user->getEmail()->getConfirmationToken());
    }

    public function testAuthorization(): void
    {
        $command = new ConfirmEmailCommand('some-token');

        $handler = new ConfirmEmailHandler(
            $this->commandBus,
            $this->objectManager,
            $this->getUserRepository($this->generateUser())
        );

        $handler->handle($command);

        $this->assertInstanceOf(AuthorizeTrustedUserCommand::class, $this->commandBus->getLastHandledCommand());
    }

    private function getUserRepository(?User $user = null): UserRepository
    {
        $repository = $this->createMock(UserRepository::class);

        $repository->method('findOneByConfirmationEmailToken')
            ->willReturn($user);

        return $repository;
    }
}
