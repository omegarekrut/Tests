<?php

namespace Tests\Functional\Domain\CompanyLetter\Command\GreetingBlock;

use App\Domain\CompanyLetter\Command\GreetingBlock\UpdateGreetingBlockCommand;
use App\Domain\CompanyLetter\Entity\GreetingBlock;
use Carbon\Carbon;
use Tests\DataFixtures\ORM\CompanyLetter\LoadGreetingBlockPreviousMonth;
use Tests\Functional\ValidationTestCase;

class UpdateGreetingBlockCommandValidationTest extends ValidationTestCase
{
    private UpdateGreetingBlockCommand $command;

    protected function setUp(): void
    {
        parent::setUp();

        $referenceRepository = $this->loadFixtures([
            LoadGreetingBlockPreviousMonth::class,
        ])->getReferenceRepository();

        $greetingBlock = $referenceRepository->getReference(LoadGreetingBlockPreviousMonth::REFERENCE_NAME);
        assert($greetingBlock instanceof GreetingBlock);

        $this->command = new UpdateGreetingBlockCommand($greetingBlock);
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
