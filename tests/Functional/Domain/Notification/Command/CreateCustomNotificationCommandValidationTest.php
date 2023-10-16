<?php

namespace Tests\Functional\Domain\Notification\Command;

use App\Domain\Notification\Command\CreateCustomNotificationCommand;
use App\Domain\User\Entity\User;
use Ramsey\Uuid\Uuid;
use Tests\Functional\ValidationTestCase;

/**
 * @group notification
 */
class CreateCustomNotificationCommandValidationTest extends ValidationTestCase
{
    private $command;

    protected function setUp(): void
    {
        parent::setUp();

        $this->command = new CreateCustomNotificationCommand(Uuid::uuid4(), $this->createMock(User::class));
    }

    protected function tearDown(): void
    {
        unset($this->command);

        parent::tearDown();
    }

    public function testNotBlankFields(): void
    {
        $this->assertOnlyFieldsAreInvalid($this->command, ['message'], null, 'Это поле обязательно для заполнения.');
    }

    public function testInvalidLengthFields(): void
    {
        $this->assertOnlyFieldsAreInvalid($this->command, ['title'], $this->getFaker()->realText(300), 'Длина не должна превышать 255 символов.');
    }

    public function testValidationShouldBePassedForCorrectFilledCommand(): void
    {
        $this->command->title = 'title';
        $this->command->message = 'message';

        $this->getValidator()->validate($this->command);

        $this->assertCount(0, $this->getValidator()->getLastErrors());
    }
}
