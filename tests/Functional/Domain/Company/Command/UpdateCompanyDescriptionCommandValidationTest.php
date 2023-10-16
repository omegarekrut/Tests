<?php

namespace Tests\Functional\Domain\Company\Command;

use App\Domain\Company\Command\UpdateCompanyDescriptionCommand;
use App\Domain\Company\Command\UpdateCompanyDescriptionCommandFactory;
use Tests\DataFixtures\ORM\Company\Company\LoadAquaMotorcycleShopsCompany;
use Tests\Functional\ValidationTestCase;

/**
 * @group update-company
 */
class UpdateCompanyDescriptionCommandValidationTest extends ValidationTestCase
{
    private UpdateCompanyDescriptionCommand $command;

    protected function setUp(): void
    {
        parent::setUp();

        $referenceRepository = $this->loadFixtures([
            LoadAquaMotorcycleShopsCompany::class,
        ])->getReferenceRepository();

        $company = $referenceRepository->getReference(LoadAquaMotorcycleShopsCompany::REFERENCE_NAME);

        $this->command = UpdateCompanyDescriptionCommandFactory::create($company);
        $this->command->videoUrls = [
            'https://www.youtube.com/watch?v=-964sSBviK0&ab_channel=Jamie%27sDesign',
        ];
    }

    protected function tearDown(): void
    {
        unset($this->command);

        parent::tearDown();
    }

    public function testPreviewContainTooMuchUpperCase(): void
    {
        $this->assertOnlyFieldsAreInvalid($this->command, ['description'], mb_strtoupper($this->getFaker()->realText(50)), 'Описания, состоящие в большинстве из заглавных букв, запрещены.');
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

    public function testValid(): void
    {
        $this->getValidator()->validate($this->command);

        $errors = $this->getValidator()->getLastErrors();

        $this->assertCount(0, $errors);
    }
}
