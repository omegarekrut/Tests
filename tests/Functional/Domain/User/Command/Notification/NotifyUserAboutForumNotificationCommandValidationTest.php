<?php

namespace Tests\Functional\Domain\User\Command\Notification;

use App\Domain\User\Command\Notification\NotifyUserAboutForumNotificationCommand;
use App\Domain\User\Entity\User;
use Tests\Functional\ValidationTestCase;

/**
 * @group notification
 */
class NotifyUserAboutForumNotificationCommandValidationTest extends ValidationTestCase
{
    /** @var NotifyUserAboutForumNotificationCommand */
    private $command;

    protected function setUp(): void
    {
        parent::setUp();

        $this->command = new NotifyUserAboutForumNotificationCommand(
            $this->createMock(User::class),
            $this->createMock(User::class)
        );
    }

    protected function tearDown(): void
    {
        unset($this->command);

        parent::tearDown();
    }

    public function testAllForwardedDataFromForumAreRequired(): void
    {
        $this->getValidator()->validate($this->command);

        $this->assertFieldInvalid('forumNotificationId', 'Значение не должно быть пустым.');
        $this->assertFieldInvalid('forumMessage', 'Значение не должно быть пустым.');
        $this->assertFieldInvalid('forumCategory', 'Значение не должно быть пустым.');
    }

    public function testCommandFilledWithCorrectDataShouldNotCauseErrors(): void
    {
        $this->command->forumNotificationId = 1;
        $this->command->forumMessage = 'some message';
        $this->command->forumCategory = 'any category';

        $this->getValidator()->validate($this->command);

        $this->assertEmpty($this->getValidator()->getLastErrors());
    }
}
