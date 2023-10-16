<?php

namespace Tests\Functional\Domain\CompanyLetter\Command\GreetingBlock;

use App\Domain\CompanyLetter\Command\GreetingBlock\CreateGreetingBlockCommand;
use Carbon\Carbon;
use Ramsey\Uuid\Uuid;
use Tests\Functional\ValidationTestCase;

class CreateGreetingBlockCommandValidationTest extends ValidationTestCase
{
    private CreateGreetingBlockCommand $command;

    protected function setUp(): void
    {
        parent::setUp();

        $this->command = new CreateGreetingBlockCommand(Uuid::uuid4());
    }

    protected function tearDown(): void
    {
        unset($this->command);

        parent::tearDown();
    }

    public function testNotBlankFields(): void
    {
        $this->assertOnlyFieldsAreInvalid($this->command, ['data', 'startAt', 'finishAt'], null, 'Это поле обязательно для заполнения.');
    }

    public function testInvalidDatetimeField(): void
    {
        $this->assertOnlyFieldsAreInvalid($this->command, ['startAt', 'finishAt'], $this->getFaker()->realText(10), 'Значение даты и времени недопустимо.');
    }

    public function testPeriodCannotBeFinishEarlierThenStarted(): void
    {
        $this->command->data = 'CompanyMailingBlock data';
        $this->command->startAt = Carbon::now();
        $this->command->finishAt = Carbon::now()->subDay();

        $this->getValidator()->validate($this->command);

        $this->assertFieldInvalid('startAt', 'Дата начала рассылки должна быть меньше, чем дата окончания.');
    }

    public function testValidationShouldBePassedForCorrectFilledCommand(): void
    {
        $this->command->data = 'CompanyMailingBlock data';
        $this->command->startAt = Carbon::now()->subDay();
        $this->command->finishAt = Carbon::now();

        $this->getValidator()->validate($this->command);

        $this->assertCount(0, $this->getValidator()->getLastErrors());
    }
}
