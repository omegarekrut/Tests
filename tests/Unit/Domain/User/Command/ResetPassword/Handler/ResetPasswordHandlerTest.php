<?php

namespace Tests\Unit\Domain\User\Command\ResetPassword\Handler;

use App\Bridge\Xenforo\ForumApi;
use App\Bridge\Xenforo\ForumApiInterface;
use App\Bridge\Xenforo\Provider\Mock\UserProvider;
use App\Domain\User\Command\ResetPassword\Handler\ResetPasswordHandler;
use App\Domain\User\Command\ResetPassword\ResetPasswordCommand;
use App\Domain\User\Entity\User;
use App\Domain\User\Repository\UserRepository;
use Tests\Unit\Mock\UserPasswordEncoderMock;
use Tests\Unit\TestCase;

/**
 * @group reset-password
 */
class ResetPasswordHandlerTest extends TestCase
{
    /** @var UserPasswordEncoderMock */
    private $userPasswordEncoderMock;
    /** @var ForumApiInterface */
    private $forumApi;

    protected function setUp(): void
    {
        $this->userPasswordEncoderMock = new UserPasswordEncoderMock();
        $this->forumApi = $this->createForumApiWithMockUserProvider();
    }

    protected function tearDown(): void
    {
        unset (
            $this->userPasswordEncoderMock,
            $this->forumApi
        );

        parent::tearDown();
    }

    public function testUserMustReceiveNewPasswordAfterHandling(): void
    {
        $user = $this->generateUser();
        $user->setForumUserId(1);

        $userRepository = $this->createUserRepositoryForFindByToken($user);

        $handler = new ResetPasswordHandler(
            $userRepository,
            $this->forumApi,
            $this->userPasswordEncoderMock
        );

        $command = new ResetPasswordCommand('token');
        $command->newPassword = 'password';
        $handler->handle($command);

        $this->assertEquals($command->newPassword, $user->getPassword());

    }

    public function testPasswordMustBeUpdatedAlsoInForum()
    {
        $user = $this->generateUser();
        $user->setForumUserId(1);

        $userRepository = $this->createUserRepositoryForFindByToken($user);

        $handler = new ResetPasswordHandler(
            $userRepository,
            $this->forumApi,
            $this->userPasswordEncoderMock
        );

        $command = new ResetPasswordCommand('token');
        $command->newPassword = 'password';

        $handler->handle($command);

        /** @var UserProvider $forumUserProvider */
        $forumUserProvider = $this->forumApi->user();

        $this->assertEquals($user->getForumUserId(), $forumUserProvider->getUserId());
        $this->assertEquals($command->newPassword, $forumUserProvider->getPassword());
    }

    private function createUserRepositoryForFindByToken(User $user): UserRepository
    {
        $stub = $this->createMock(UserRepository::class);
        $stub
            ->method('findOneByResetPasswordToken')
            ->willReturn($user);

        return $stub;
    }

    private function createForumApiWithMockUserProvider(): ForumApi
    {
        $userProvider = new UserProvider();

        $stub = new ForumApi();
        $stub->addProvider($userProvider);

        return $stub;
    }
}
