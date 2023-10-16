<?php

namespace Tests\Unit\Domain\Warning\Command\Handler;

use App\Bridge\Xenforo\ForumApi;
use App\Bridge\Xenforo\Provider\Mock\MessageProvider;
use App\Domain\User\Entity\User;
use App\Domain\Warning\Command\Handler\SendWarningHandler;
use App\Domain\Warning\Command\SendWarningCommand;
use App\Domain\Warning\Exception\WarnedUserNotExistsException;
use App\Module\Author\AuthorInterface;
use Tests\Unit\TestCase;

class SendWarningHandlerTest extends TestCase
{
    public function testSendPrivateMessageOnForum(): void
    {
        $forumApi = $this->getMockForumApi();

        $handler = new SendWarningHandler(
            $forumApi
        );

        $command = new SendWarningCommand($this->getMockUser(), $this->getMockAuthor());
        $command->text = 'warning text';

        $handler->handle($command);

        $this->assertEquals(1, $forumApi->message()->getFromUserId());
        $this->assertEquals(2, $forumApi->message()->getToUserId());
        $this->assertEquals('Вам вынесено предупреждение.', $forumApi->message()->getSubject());
        $this->assertEquals('warning text', $forumApi->message()->getMessage());

        $forumApi->message()->flush();
    }

    public function testSendingFailToUnknownAuthor(): void
    {
        $this->expectException(WarnedUserNotExistsException::class);

        $forumApi = $this->getMockForumApi();

        $handler = new SendWarningHandler(
            $forumApi
        );

        $command = new SendWarningCommand($this->getMockUser(), $this->getMockAuthorUnknown());
        $command->text = 'warning text';

        $handler->handle($command);
    }

    private function getMockForumApi(): ForumApi
    {
        $messageProvider = new MessageProvider();

        $stub = new ForumApi();
        $stub->addProvider($messageProvider);

        return $stub;
    }

    private function getMockUser(): User
    {
        $stub = $this->createMock(User::class);

        $stub
            ->method('getForumUserId')
            ->willReturn(1);

        return $stub;
    }

    private function getMockAuthor(): AuthorInterface
    {
        $stub = $this->createMock(User::class);

        $stub
            ->method('getForumUserId')
            ->willReturn(2);

        return $stub;
    }

    private function getMockAuthorUnknown(): AuthorInterface
    {
        $stub = $this->createMock(AuthorInterface::class);
        $stub
            ->method('getId')
            ->willReturn(null);

        return $stub;
    }
}
