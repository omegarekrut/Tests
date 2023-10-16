<?php

namespace Tests\Unit\Domain\User\Command\Notification;

use App\Domain\User\Collection\UserCollection;
use App\Domain\User\Command\Notification\DeleteOldNotificationsCommand;
use App\Domain\User\Command\Notification\DeleteOldNotificationsForAllUsersCommand;
use App\Domain\User\Command\Notification\Handler\DeleteOldNotificationsForAllUsersHandler;
use App\Domain\User\Entity\User;
use App\Domain\User\Repository\UserRepository;
use Tests\Unit\Mock\CommandBusMock;
use Tests\Unit\TestCase;

class DeleteOldNotificationsForAllUsersHandlerTest extends TestCase
{
    private $commandBus;
    private $command;

    protected function setUp(): void
    {
        parent::setUp();

        $this->commandBus = new CommandBusMock();
        $this->command = new DeleteOldNotificationsForAllUsersCommand();
    }

    protected function tearDown(): void
    {
        unset(
            $this->commandBus,
            $this->command
        );

        parent::tearDown();
    }

    public function testHandle(): void
    {
        $user = $this->createUserMock(1);
        $userRepository = $this->createUserRepositoryMock([$user]);

        $commandHandler = new DeleteOldNotificationsForAllUsersHandler($this->commandBus, $userRepository);

        $commandHandler->handle($this->command);

        /** @var DeleteOldNotificationsCommand $deleteOldNotificationsCommand */
        $deleteOldNotificationsCommand = $this->commandBus->getLastHandledCommand();


        $this->assertCount(1, $this->commandBus->getAllHandledCommands());
        $this->assertInstanceOf(DeleteOldNotificationsCommand::class, $deleteOldNotificationsCommand);
        $this->assertEquals($user->getId(), $deleteOldNotificationsCommand->getUserId());
    }

    private function createUserMock(int $id): User
    {
        $user = $this->createMock(User::class);

        $user
            ->method('getId')
            ->willReturn($id);

        return $user;
    }

    /**
     * @param User[] $usersWithOldNotifications
     */
    private function createUserRepositoryMock(array $usersWithOldNotifications): UserRepository
    {
        $userRepository = $this->createMock(UserRepository::class);

        $userRepository
            ->method('findAllWithOldNotifications')
            ->willReturn(new UserCollection($usersWithOldNotifications));

        return $userRepository;
    }
}
