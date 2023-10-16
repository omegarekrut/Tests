<?php

namespace Tests\Functional\Domain\User\Command\Profile;

use App\Domain\User\Command\Profile\UpdateFishingInformationCommand;
use App\Domain\User\Entity\ValueObject\FishingInformation;
use Tests\Functional\ValidationTestCase;
use Tests\Traits\UserGeneratorTrait;

class UpdateFishingInformationCommandValidationTest extends ValidationTestCase
{
    use UserGeneratorTrait;

    /** @var UpdateFishingInformationCommand */
    private $command;

    protected function setUp(): void
    {
        parent::setUp();

        $this->command = new UpdateFishingInformationCommand($this->generateUser());
    }

    protected function tearDown(): void
    {
        unset($this->command);

        parent::tearDown();
    }

    public function testStringLength(): void
    {
        $this->command->fishingTime = $this->getFaker()->realText(300);
        $this->command->watercraft = $this->getFaker()->realText(300);
        $this->command->aboutMe = $this->getFaker()->realText(4100);

        $this->getValidator()->validate($this->command);

        $this->assertFieldInvalid('fishingTime', 'Максимальная длина 255 символов.');
        $this->assertFieldInvalid('watercraft', 'Максимальная длина 255 символов.');
        $this->assertFieldInvalid('aboutMe', 'Максимальная длина 4000 символов.');
    }

    public function testInvalidFishingTypes(): void
    {
        $this->command->fishingTypes = ['fishingTypes'];

        $this->getValidator()->validate($this->command);

        $this->assertFieldInvalid('fishingTypes', 'Одно или несколько заданных значений недопустимо.');
    }

    public function testInvalidFishingForYou(): void
    {
        $this->command->fishingForYou = ['fishingForYou'];

        $this->getValidator()->validate($this->command);

        $this->assertFieldInvalid('fishingForYou', 'Одно или несколько заданных значений недопустимо.');
    }

    public function testInvalidFishingTime(): void
    {
        $this->command->fishingTime = 'fishingTime';

        $this->getValidator()->validate($this->command);

        $this->assertFieldInvalid('fishingTime', 'Выбранное Вами значение недопустимо.');
    }

    public function testInvalidhaveWatercraft(): void
    {
        $this->command->haveWatercraft = 'haveWatercraft';

        $this->getValidator()->validate($this->command);

        $this->assertFieldInvalid('haveWatercraft', 'Тип значения должен быть boolean.');
    }

    public function testNotFilledCommandShouldNotCauseErrors(): void
    {
        $this->getValidator()->validate($this->command);

        $this->assertEmpty($this->getValidator()->getLastErrors());
    }

    public function testCommandFilledWithCorrectDataShouldNotCauseErrors(): void
    {
        $this->command->fishingForYou = [FishingInformation::FISHING_FOR_YOU_CHOICE[0]];
        $this->command->fishingTypes = [FishingInformation::FISHING_TYPES[0]];
        $this->command->fishingTime = FishingInformation::FISHING_TIME[0];
        $this->command->aboutMe = 'Супер рыбак';
        $this->command->watercraft = 'Волжанка 47';
        $this->command->haveWatercraft = true;

        $this->getValidator()->validate($this->command);

        $this->assertEmpty($this->getValidator()->getLastErrors());
    }
}
