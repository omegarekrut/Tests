<?php

namespace Tests\Functional\Domain\MailingBlockAd\Command;

use App\Domain\MailingBlockAd\Command\UpdateMailingBlockAdCommand;
use App\Util\ImageStorage\Image;
use Carbon\Carbon;
use Tests\DataFixtures\ORM\MailingBlockAd\LoadMailingBlockAd;
use Tests\Functional\ValidationTestCase;

class UpdateMailingBlockAdCommandValidationTest extends ValidationTestCase
{
    private UpdateMailingBlockAdCommand $command;

    protected function setUp(): void
    {
        parent::setUp();

        $referenceRepository = $this->loadFixtures([
            LoadMailingBlockAd::class,
        ])->getReferenceRepository();

        $mailingBlockAd = $referenceRepository->getReference(LoadMailingBlockAd::REFERENCE_NAME);

        $this->command = new UpdateMailingBlockAdCommand($mailingBlockAd);
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
        $this->command->title = 'MailingBlockAd title';
        $this->command->data = 'MailingBlockAd data';
        $this->command->image = new Image('image.jpg');
        $this->command->startAt = Carbon::now();
        $this->command->finishAt = Carbon::now()->subDay();

        $this->getValidator()->validate($this->command);

        $this->assertFieldInvalid('startAt', 'Дата начала рассылки должна быть меньше, чем дата окончания.');
    }

    public function testValidationShouldBePassedForCorrectFilledCommand(): void
    {
        $this->command->title = 'MailingBlockAd title';
        $this->command->data = 'MailingBlockAd data';
        $this->command->image = new Image('image.jpg');
        $this->command->startAt = Carbon::now()->subDay();
        $this->command->finishAt = Carbon::now();

        $this->getValidator()->validate($this->command);

        $this->assertCount(0, $this->getValidator()->getLastErrors());
    }
}
