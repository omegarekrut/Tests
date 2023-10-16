<?php

namespace Tests\Unit\Domain\User\Command\ChangePassword\Handler;

use App\Bridge\Xenforo\ForumApi;
use App\Bridge\Xenforo\Provider\Mock\UserProvider;
use App\Domain\User\Command\ChangePassword\ChangePasswordCommand;
use App\Domain\User\Command\ChangePassword\Handler\ChangePasswordHandler;
use App\Domain\User\Entity\User;
use Tests\Unit\Mock\UserPasswordEncoderMock;
use Tests\Unit\Mock\ObjectManagerMock;
use Tests\Unit\TestCase;

/**
 * @group change-password
 */
class ChangePasswordHandlerTest extends TestCase
{
    /** @var ChangePasswordCommand */
    private $command;
    private $forumApi;

    protected function setUp(): void
    {
        parent::setUp();

        $this->command = new ChangePasswordCommand($this->getUser());
        $this->command->newPassword = 'new-password';

        $this->forumApi = new ForumApi();
        $this->forumApi->addProvider(new UserProvider());
    }

    protected function tearDown(): void
    {
        unset (
            $this->command,
            $this->forumApi
        );

        parent::tearDown();
    }

    public function testPasswordIsChanged(): void
    {
        $handler = new ChangePasswordHandler(
            $this->forumApi,
            new ObjectManagerMock(),
            new UserPasswordEncoderMock()
        );

        $handler->handle($this->command);

        $this->assertEquals($this->command->newPassword, $this->command->getUser()->getPassword());
    }

    public function testSyncPasswordWithForum(): void
    {
        $handler = new ChangePasswordHandler(
            $this->forumApi,
            new ObjectManagerMock(),
            new UserPasswordEncoderMock()
        );

        $handler->handle($this->command);

        $this->assertEquals($this->command->getUser()->getForumUserId(), $this->forumApi->user()->getUserId());
    }

    private function getUser(): User
    {
        $user = $this->generateUser();
        $user->setForumUserId(142);

        return $user;
    }
}
