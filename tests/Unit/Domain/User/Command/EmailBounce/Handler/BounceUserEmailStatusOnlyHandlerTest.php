<?php

namespace Tests\Unit\Domain\User\Command\EmailBounce\Handler;

use App\Domain\User\Command\EmailBounce\Handler\BounceUserEmailStatusOnlyHandler;
use App\Domain\User\Command\EmailBounce\BounceUserEmailStatusOnlyCommand;
use App\Domain\User\Entity\User;
use App\Domain\User\Repository\UserRepository;
use Tests\Unit\Mock\CommandBusMock;
use Tests\Unit\Mock\ObjectManagerMock;
use Tests\Unit\TestCase;

class BounceUserEmailStatusOnlyHandlerTest extends TestCase
{
    private $commandBus;
    private $objectManager;

    protected function setUp(): void
    {
        parent::setUp();

        $this->commandBus = new CommandBusMock();
        $this->objectManager = new ObjectManagerMock();
    }

    protected function tearDown(): void
    {
        unset(
            $this->commandBus,
            $this->objectManager
        );
    }

    public function testUserMustBeBouncedAfterHandling(): void
    {
        $user = $this->generateUser();

        $command = new BounceUserEmailStatusOnlyCommand();
        $command->email = $user->getEmailAddress();

        $handler = new BounceUserEmailStatusOnlyHandler($this->objectManager, $this->getUserRepository($user));
        $handler->handle($command);

        $this->assertTrue($user->getEmail()->isBounced());
    }

    private function getUserRepository(User $user): UserRepository
    {
        $stub = $this->createMock(UserRepository::class);
        $stub
            ->method('findOneByLoginOrEmail')
            ->willReturn($user);

        return $stub;
    }
}
