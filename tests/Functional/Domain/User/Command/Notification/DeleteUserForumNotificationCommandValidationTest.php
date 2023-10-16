<?php

namespace Tests\Functional\Domain\User\Command\Notification;

use App\Domain\User\Command\Notification\DeleteUserForumNotificationCommand;
use App\Domain\User\Entity\User;
use Tests\Functional\ValidationTestCase;

/**
 * @group notification
 */
class DeleteUserForumNotificationCommandValidationTest extends ValidationTestCase
{
    /** @var DeleteUserForumNotificationCommand */
    private $command;

    protected function setUp(): void
    {
        parent::setUp();

        $this->command = new DeleteUserForumNotificationCommand($this->createMock(User::class));
    }

    protected function tearDown(): void
    {
        unset($this->command);

        parent::tearDown();
    }

    public function testForumNotificationIdIsRequired(): void
    {
        $this->getValidator()->validate($this->command);

        $this->assertFieldInvalid('forumNotificationId', 'Значение не должно быть пустым.');
    }

    public function testCommandFilledWithCorrectDataShouldNotCauseErrors(): void
    {
        $this->command->forumNotificationId = 1;

        $this->getValidator()->validate($this->command);

        $this->assertEmpty($this->getValidator()->getLastErrors());
    }
}
