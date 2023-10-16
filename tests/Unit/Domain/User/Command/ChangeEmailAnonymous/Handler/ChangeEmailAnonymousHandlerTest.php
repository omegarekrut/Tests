<?php

namespace Tests\Unit\Domain\User\Command\ChangeEmailAnonymous\Handler;

use App\Domain\User\Command\ChangeEmailAnonymous\ChangeEmailAnonymousCommand;
use App\Domain\User\Command\ChangeEmailAnonymous\Handler\ChangeEmailAnonymousHandler;
use App\Domain\User\Command\SendConfirmationEmail\SendConfirmationEmailCommand;
use App\Domain\User\Entity\User;
use App\Domain\User\Repository\UserRepository;
use Tests\Unit\Mock\CommandBusMock;
use Tests\Unit\TestCase;

class ChangeEmailAnonymousHandlerTest extends TestCase
{
    public function testEmailIsChanged(): void
    {
        $user = $this->getUser();

        $commandBus = new CommandBusMock();
        $userRepository = $this->getUserRepository($user);

        $command = new ChangeEmailAnonymousCommand();
        $command->loginOrEmail = 'login';
        $command->password = 'password';
        $command->email = '111@test.ru';

        $handler = new ChangeEmailAnonymousHandler(
            $commandBus,
            $userRepository
        );
        $handler->handle($command);

        $this->assertEquals($command->email, $user->getEmailAddress());
    }

    private function getUserRepository(?User $user): UserRepository
    {
        $repository = $this->createMock(UserRepository::class);

        $repository->method('findOneByLoginOrEmail')
            ->willReturn($user);

        return $repository;
    }

    public function testIsCallSendConfirmationEmailCommand(): void
    {
        $user = $this->getUser();

        $commandBus = new CommandBusMock();
        $userRepository = $this->getUserRepository($user);

        $command = new ChangeEmailAnonymousCommand();
        $command->loginOrEmail = 'test';
        $command->password = 'password';
        $command->email = 'user@test.ru';

        $handler = new ChangeEmailAnonymousHandler(
            $commandBus,
            $userRepository
        );
        $handler->handle($command);

        $lastHandledCommand = $commandBus->getLastHandledCommand();
        $this->assertInstanceOf(SendConfirmationEmailCommand::class, $lastHandledCommand);
        $this->assertEquals($user, $lastHandledCommand->getUser());
    }

    private function getUser(): User
    {
        $user = $this->generateUser();

        return $user;
    }
}
