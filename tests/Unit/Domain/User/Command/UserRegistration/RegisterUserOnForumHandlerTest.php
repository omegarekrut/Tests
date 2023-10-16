<?php

namespace Tests\Unit\Domain\User\Command\UserRegistration;

use App\Bridge\Xenforo\ForumApi;
use App\Bridge\Xenforo\Provider\Mock\UserProvider;
use App\Domain\User\Command\UserRegistration\Handler\RegisterUserOnForumHandler;
use App\Domain\User\Command\UserRegistration\RegisterUserOnForumCommand;
use App\Domain\User\Entity\User;
use App\Domain\User\Exception\ForumSyncException;
use App\Domain\User\Repository\UserRepository;
use Tests\Unit\TestCase;

/**
 * @group registration
 */
class RegisterUserOnForumHandlerTest extends TestCase
{
    /** @var ForumApi */
    private $forumApi;

    protected function setUp(): void
    {
        parent::setUp();

        $userProvider = new UserProvider();

        $this->forumApi = new ForumApi();
        $this->forumApi->addProvider($userProvider);
    }

    public function testHandle(): void
    {
        $this->forumApi->user()->setUserId(42);

        $command = new RegisterUserOnForumCommand();
        $command->userId = 1;
        $command->plainPassword = 'password';

        $handler = new RegisterUserOnForumHandler($this->forumApi, $this->createUserRepository(1, 42));

        $handler->handle($command);
    }

    public function testFailureHandle(): void
    {
        $this->expectException(ForumSyncException::class);

        $command = new RegisterUserOnForumCommand();
        $command->userId = 1;
        $command->plainPassword = 'password';

        $handler = new RegisterUserOnForumHandler($this->forumApi, $this->createUserRepository(1));

        $handler->handle($command);
    }

    private function createUserRepository(?int $userId = null, ?int $forumUserId = null): UserRepository
    {
        $stub = $this->createMock(UserRepository::class);
        $stub
            ->expects($this->once())
            ->method('findById')
            ->with($userId)
            ->willReturn($this->generateUser());
        $stub
            ->expects($forumUserId ? $this->once() : $this->never())
            ->method('save')
            ->willReturnCallback(function (User $user) use ($userId, $forumUserId) {
                $this->assertEquals($forumUserId, $user->getForumUserId());
            });

        return $stub;
    }
}
