<?php

namespace Tests\Unit\Domain\User\Command\UserDeletion\Handler;

use App\Bridge\Xenforo\ForumApi;
use App\Bridge\Xenforo\ForumApiInterface;
use App\Bridge\Xenforo\Provider\Mock\UserProvider;
use App\Domain\User\Command\Deleting\AnonymizeAllUserCreatedContentCommand;
use App\Domain\User\Command\Deleting\DeleteUserWithoutDeletingContentCommand;
use App\Domain\User\Command\Deleting\Handler\DeleteUserWithoutDeletingContentHandler;
use App\Domain\User\Entity\User;
use App\Domain\User\Repository\UserRepository;
use Tests\Unit\Mock\CommandBusMock;
use Tests\Unit\Mock\IgnoreDoctrineFiltersAndSoftDeletableEnvironmentMock;
use Tests\Unit\TestCase;

class DeleteUserWithoutDeletingContentHandlerTest extends TestCase
{
    private const USER_ID = 12;
    private const USER_FORUM_ID = 122;

    private $commandBus;

    protected function setUp(): void
    {
        parent::setUp();

        $this->commandBus = new CommandBusMock();
    }

    protected function tearDown(): void
    {
        unset($this->commandBus);

        parent::tearDown();
    }

    /**  */
    public function testContentAnonymizationCommandWasCalled(): void
    {
        $user = $this->createUser(self::USER_ID, 0);

        $handler = new DeleteUserWithoutDeletingContentHandler(
            $this->createUserRepository($user),
            $this->commandBus,
            $this->createMock(ForumApiInterface::class),
            new IgnoreDoctrineFiltersAndSoftDeletableEnvironmentMock()
        );

        $command = new DeleteUserWithoutDeletingContentCommand();
        $command->userId = $user->getId();

        $handler->handle($command);

        $this->assertTrue($this->commandBus->isHandled(AnonymizeAllUserCreatedContentCommand::class));
    }

    public function testMethodForForumUserDeletionWasCalled(): void
    {
        $user = $this->createUser(self::USER_ID, self::USER_FORUM_ID);

        $forumApi = $this->createForumApi();

        $handler = new DeleteUserWithoutDeletingContentHandler(
            $this->createUserRepository($user),
            $this->commandBus,
            $forumApi,
            new IgnoreDoctrineFiltersAndSoftDeletableEnvironmentMock()
        );

        $command = new DeleteUserWithoutDeletingContentCommand();
        $command->userId = $user->getId();

        $handler->handle($command);

        $this->assertTrue($forumApi->user()->isSoftDeleted(self::USER_FORUM_ID));
    }

    private function createForumApi(): ForumApiInterface
    {
        $userProvider = new UserProvider();

        $forumApi = new ForumApi();
        $forumApi->addProvider($userProvider);

        return $forumApi;
    }

    private function createUserRepository(User $user): UserRepository
    {
        $userRepository = $this->createMock(UserRepository::class);
        $userRepository
            ->method('findById')
            ->willReturn($user);

        return $userRepository;
    }

    private function createUser(int $userId, int $userForumId): User
    {
        $user = $this->createMock(User::class);
        $user
            ->method('detachLinkedAccounts')
            ->willReturn($user);
        $user
            ->method('getForumUserId')
            ->willReturn($userForumId);
        $user
            ->method('getId')
            ->willReturn($userId);

        return $user;
    }
}
