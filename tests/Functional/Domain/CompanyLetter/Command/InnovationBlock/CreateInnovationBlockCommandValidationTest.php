<?php

namespace Tests\Functional\Domain\CompanyLetter\Command\InnovationBlock;

use App\Domain\CompanyLetter\Command\InnovationBlock\CreateInnovationBlockCommand;
use App\Util\ImageStorage\Image;
use Carbon\Carbon;
use Tests\Functional\ValidationTestCase;

class CreateInnovationBlockCommandValidationTest extends ValidationTestCase
{
    private CreateInnovationBlockCommand $command;

    protected function setUp(): void
    {
        parent::setUp();

        $this->command = new CreateInnovationBlockCommand();
    }

    protected function tearDown(): void
    {
        unset($this->command);

        parent::tearDown();
    }

    public function testNotBlankFields(): void
    {
        $this->assertOnlyFieldsAreInvalid($this->command, ['title', 'data', 'image', 'startAt', 'finishAt'], null, 'Это поле обязательно для заполнения.');
    }

    public function testInvalidDatetimeField(): void
    {
        $this->assertOnlyFieldsAreInvalid($this->command, ['startAt', 'finishAt'], $this->getFaker()->realText(10), 'Значение даты и времени недопустимо.');
    }

    public function testPeriodCannotBeFinishEarlierThenStarted(): void
    {
        $this->command->title = 'CompanyLetter title';
        $this->command->data = 'CompanyLetter data';
        $this->command->image = new Image('image.jpg');
        $this->command->startAt = Carbon::now();
        $this->command->finishAt = Carbon::now()->subDay();

        $this->getValidator()->validate($this->command);

        $this->assertFieldInvalid('startAt', 'Дата начала рассылки должна быть меньше, чем дата окончания.');
    }

    public function testValidationShouldBePassedForCorrectFilledCommand(): void
    {
        $this->command->title = 'CompanyLetter title';
        $this->command->data = 'CompanyLetter data';
        $this->command->image = new Image('image.jpg');
        $this->command->startAt = Carbon::now()->subDay();
        $this->command->finishAt = Carbon::now();

        $this->getValidator()->validate($this->command);

        $this->assertCount(0, $this->getValidator()->getLastErrors());
    }
}
