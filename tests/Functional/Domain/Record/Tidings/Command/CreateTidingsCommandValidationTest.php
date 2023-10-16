<?php

namespace Tests\Functional\Domain\Record\Tidings\Command;

use App\Domain\Company\Entity\Company;
use App\Domain\Record\Tidings\Command\CreateTidingsCommand;
use App\Domain\User\Entity\User;
use Carbon\Carbon;
use Tests\DataFixtures\ORM\Company\Company\LoadAquaMotorcycleShopsCompany;
use Tests\DataFixtures\ORM\Region\Region\LoadTestRegion;
use Tests\DataFixtures\ORM\User\LoadUserWithAvatar;
use Tests\Functional\ValidationTestCase;

class CreateTidingsCommandValidationTest extends ValidationTestCase
{
    private CreateTidingsCommand $command;
    private Company $company;

    protected function setUp(): void
    {
        parent::setUp();

        $referenceRepository = $this->loadFixtures([
            LoadUserWithAvatar::class,
            LoadAquaMotorcycleShopsCompany::class,
            LoadTestRegion::class,
        ])->getReferenceRepository();

        $this->company = $referenceRepository->getReference(LoadAquaMotorcycleShopsCompany::REFERENCE_NAME);
        $author = $referenceRepository->getReference(LoadUserWithAvatar::REFERENCE_NAME);
        assert($author instanceof User);

        $region = $referenceRepository->getReference(LoadTestRegion::REFERENCE_NAME);

        $this->command = new CreateTidingsCommand($author);
        $now = Carbon::now();

        $this->command->title = 'Tidings title created';
        $this->command->text = 'Tidings text';
        $this->command->fishingMethods = [
            'спиннинг',
            'поплавочная удочка',
        ];

        $this->command->startDate = $now;
        $this->command->endDate = $now->copy()->addDay();

        $this->command->videoUrls = [
            'https://www.youtube.com/watch?v=-964sSBviK0&ab_channel=Jamie%27sDesign',
        ];

        $this->command->regionId = $region->getId();
    }

    protected function tearDown(): void
    {
        unset($this->command);

        parent::tearDown();
    }

    public function testDateTimeFields(): void
    {
        $this->assertOnlyFieldsAreInvalid($this->command, ['startDate', 'endDate'], $this->getFaker()->realText(10), 'Значение даты и времени недопустимо.');
    }

    public function testNotBlankFields(): void
    {
        $this->assertOnlyFieldsAreInvalid($this->command, ['title', 'text'], null, 'Это поле обязательно для заполнения.');
    }

    public function testTooLongFields(): void
    {
        $fields = [
            'title',
            'fishingTime',
            'place',
            'tackles',
            'catch',
            'weather',
        ];

        $this->assertOnlyFieldsAreInvalid($this->command, $fields, $this->getFaker()->realText(300), 'Длина не должна превышать 255 символов.');
    }

    public function testContainTooMuchUpperCase(): void
    {
        $fakeText = $this->getFaker()->toUpper($this->getFaker()->realText(50));

        $this->assertOnlyFieldsAreInvalid($this->command, ['title', 'text'], $fakeText, 'Записи, состоящие в большинстве из заглавных букв, запрещены.');
    }

    public function testFishingMethodsField(): void
    {
        $this->assertOnlyFieldsAreInvalid($this->command, ['fishingMethods'], ['wrongValue1', 'wrongValue2'], 'Одно или несколько заданных значений недопустимо.');
    }

    public function testStartDateNullAndEndDateNotNull(): void
    {
        $this->command->startDate = null;
        $this->command->endDate = Carbon::now();

        $this->getValidator()->validate($this->command);

        $this->assertFieldInvalid('startDate', 'Дата начала должна быть указана');
    }

    public function testStartDateShouldBeLessTomorrow(): void
    {
        $this->assertOnlyFieldsAreInvalid(
            $this->command,
            ['startDate'],
            Carbon::now()->addDays(1)->addHour(),
            'Дата начала рыбалки должна быть меньше текущей'
        );
    }

    public function testEndDateShouldBeLessTomorrow(): void
    {
        $this->assertOnlyFieldsAreInvalid(
            $this->command,
            ['endDate'],
            Carbon::now()->addDays(1)->addHour(),
            'Дата окончания рыбалки должна быть меньше текущей'
        );
    }

    public function testStartDateShouldBeLessThanEndDate(): void
    {
        $now = Carbon::now();

        $this->command->startDate = $now;
        $this->command->endDate = $now->copy()->subMinute();

        $this->getValidator()->validate($this->command);

        $this->assertFieldInvalid('startDate', 'Дата начала рыбалки должна быть меньше, чем дата окончания');
        $this->assertFieldInvalid('endDate', 'Дата окончания рыбалки должна быть больше, чем дата начала');
    }

    public function testVideoUrlsFieldContainsNotUrl(): void
    {
        $this->command->videoUrls = [
            $this->getFaker()->realText(20),
        ];

        $this->getValidator()->validate($this->command);

        $this->assertFieldInvalid('videoUrls[0]', 'Значение должно быть ссылкой');
    }

    public function testVideoUrlsFieldContainsNotYoutubeUrl(): void
    {
        $this->command->videoUrls = [
            $this->getFaker()->url,
        ];

        $this->getValidator()->validate($this->command);

        $this->assertFieldInvalid('videoUrls[0]', 'Поддерживаются видео только с youtube.');
    }

    public function testVideoUrlsFieldContainsWrongVideoUrl(): void
    {
        $this->command->videoUrls = [
            'https://www.youtube.com/watch?v=WRONGYOUTUBE',
        ];

        $this->getValidator()->validate($this->command);

        $this->assertFieldInvalid('videoUrls[0]', 'Видео не содержит iframe');
    }

    public function testAuthorIsNotOwnerCompany(): void
    {
        $this->command->companyAuthor = $this->company;

        $this->getValidator()->validate($this->command);

        $validationErrors = $this->getValidator()->getLastErrors();

        foreach ($validationErrors as $error) {
            $this->assertEquals(
                sprintf('Вам не разрешено публиковать записи от имени компании %s.', $this->company->getName()),
                $error->getMessage()
            );
        }
    }

    public function testAuthorIsOwnerCompany(): void
    {
        $this->command->author = $this->company->getOwner();
        $this->command->companyAuthor = $this->company;

        $this->getValidator()->validate($this->command);

        $this->assertCount(0, $this->getValidator()->getLastErrors());
    }

    public function testValidationShouldBePassedForCorrectFilledCommand(): void
    {
        $this->getValidator()->validate($this->command);

        $this->assertCount(0, $this->getValidator()->getLastErrors());
    }
}
