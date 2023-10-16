<?php

namespace Tests\Functional\Domain\Company\Command\Handler;

use App\Domain\Company\Command\UpdateCompanySocialLinksCommand;
use App\Domain\Company\Entity\Company;
use Tests\DataFixtures\ORM\Company\Company\LoadAquaMotorcycleShopsCompany;
use Tests\Functional\TestCase;

/**
 * @group update-company
 */
class UpdateCompanySocialLinksHandlerTest extends TestCase
{
    private const NEW_VK = 'https://vk.com/1';
    private const NEW_OK = 'https://ok.ru/1';
    private const NEW_INSTAGRAM = 'https://instagram.com/1';
    private const NEW_FACEBOOK = 'https://facebook.com/1';
    private const NEW_YOUTUBE = 'https://youtube.com/1';
    private const NEW_TELEGRAM = 'https://t.me/1';
    private const NEW_YANDEX_ZEN = 'https://zen.yandex.ru/1';

    private Company $company;

    protected function setUp(): void
    {
        parent::setUp();

        $referenceRepository = $this->loadFixtures([
            LoadAquaMotorcycleShopsCompany::class,
        ])->getReferenceRepository();

        $this->company = $referenceRepository->getReference(LoadAquaMotorcycleShopsCompany::REFERENCE_NAME);
    }

    protected function tearDown(): void
    {
        unset($this->company);

        parent::tearDown();
    }

    public function testHandle(): void
    {
        $command = new UpdateCompanySocialLinksCommand($this->company);
        $command->vk = self::NEW_VK;
        $command->ok = self::NEW_OK;
        $command->instagram = self::NEW_INSTAGRAM;
        $command->facebook = self::NEW_FACEBOOK;
        $command->youtube = self::NEW_YOUTUBE;
        $command->telegram = self::NEW_TELEGRAM;
        $command->yandexZen = self::NEW_YANDEX_ZEN;

        $this->getCommandBus()->handle($command);

        $updatedContact = $this->company->getContact();

        $this->assertEquals(self::NEW_VK, $updatedContact->getVk());
        $this->assertEquals(self::NEW_OK, $updatedContact->getOk());
        $this->assertEquals(self::NEW_INSTAGRAM, $updatedContact->getInstagram());
        $this->assertEquals(self::NEW_FACEBOOK, $updatedContact->getFacebook());
        $this->assertEquals(self::NEW_YOUTUBE, $updatedContact->getYoutube());
        $this->assertEquals(self::NEW_TELEGRAM, $updatedContact->getTelegram());
        $this->assertEquals(self::NEW_YANDEX_ZEN, $updatedContact->getYandexZen());
    }
}
