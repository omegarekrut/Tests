<?php

namespace Tests\Functional\Domain\Company\Command;

use App\Domain\Company\Command\UpdateCompanySocialLinksCommand;
use App\Domain\Company\Entity\Company;
use Tests\DataFixtures\ORM\Company\Company\LoadAquaMotorcycleShopsCompany;
use Tests\DataFixtures\ORM\Company\Contact\LoadAquaMotorcycleShopsContact;
use Tests\Functional\ValidationTestCase;

class UpdateCompanySocialLinksCommandValidationTest extends ValidationTestCase
{
    private UpdateCompanySocialLinksCommand $command;

    protected function setUp(): void
    {
        parent::setUp();

        $referenceRepository = $this->loadFixtures([
            LoadAquaMotorcycleShopsCompany::class,
            LoadAquaMotorcycleShopsContact::class,
        ])->getReferenceRepository();

        /** @var Company $company */
        $company = $referenceRepository->getReference(LoadAquaMotorcycleShopsCompany::REFERENCE_NAME);

        $this->command = new UpdateCompanySocialLinksCommand($company);
    }

    protected function tearDown(): void
    {
        unset($this->command);

        parent::tearDown();
    }

    public function testInvalidLink(): void
    {
        $this->assertOnlyFieldsAreInvalid($this->command, ['vk', 'ok', 'instagram', 'facebook'], 'some-value', 'Значение не является допустимым URL.');
    }

    public function testInvalidVkLink(): void
    {
        $this->assertOnlyFieldsAreInvalid($this->command, ['vk'], 'http://ya.ru', 'Ссылка на социальную сеть должна начинаться с https://vk.com');
    }

    public function testInvalidOkLink(): void
    {
        $this->assertOnlyFieldsAreInvalid($this->command, ['ok'], 'http://ya.ru', 'Ссылка на социальную сеть должна начинаться с https://ok.ru');
    }

    public function testInvalidInstagramLink(): void
    {
        $this->assertOnlyFieldsAreInvalid($this->command, ['instagram'], 'http://ya.ru', 'Ссылка на социальную сеть должна начинаться с https://instagram.com');
    }

    public function testInvalidFacebookLink(): void
    {
        $this->assertOnlyFieldsAreInvalid($this->command, ['facebook'], 'http://ya.ru', 'Ссылка на социальную сеть должна начинаться с https://facebook.com');
    }

    public function testInvalidYoutubeLink(): void
    {
        $this->assertOnlyFieldsAreInvalid($this->command, ['youtube'], 'http://ya.ru', 'Ссылка на социальную сеть должна начинаться с https://youtube.com');
    }

    public function testInvalidTelegramLink(): void
    {
        $this->assertOnlyFieldsAreInvalid($this->command, ['telegram'], 'http://ya.ru', 'Ссылка на социальную сеть должна начинаться с https://t.me');
    }

    public function testInvalidYandexZenLink(): void
    {
        $this->assertOnlyFieldsAreInvalid($this->command, ['yandexZen'], 'http://ya.ru', 'Ссылка на социальную сеть должна начинаться с https://zen.yandex.ru');
    }

    public function testValidWithWwwSubdomain(): void
    {
        $this->command->instagram = 'https://www.instagram.com/1';

        $this->getValidator()->validate($this->command);

        $errors = $this->getValidator()->getLastErrors();

        $this->assertCount(0, $errors);
    }

    public function testValid(): void
    {
        $this->command->vk = 'https://vk.com/1';
        $this->command->ok = 'https://ok.ru/1';
        $this->command->instagram = 'https://instagram.com/1';
        $this->command->facebook = 'https://facebook.com/1';
        $this->command->youtube = 'https://youtube.com/1';
        $this->command->telegram = 'https://t.me/1';
        $this->command->yandexZen = 'https://zen.yandex.ru/1';

        $this->getValidator()->validate($this->command);

        $errors = $this->getValidator()->getLastErrors();

        $this->assertCount(0, $errors);
    }
}
