<?php

namespace Tests\Functional\Domain\FleaMarket\Command\Brand;

use App\Domain\FleaMarket\Command\Brand\UpdateBrandCommand;
use App\Domain\FleaMarket\Entity\Brand;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Tests\DataFixtures\ORM\FleaMarket\LoadFleaMarketBrandWithoutLogo;
use Tests\Functional\ValidationTestCase;

class UpdateBrandCommandValidationTest extends ValidationTestCase
{
    private Brand $brandToUpdate;

    protected function setUp(): void
    {
        parent::setUp();

        $referenceRepository = $this->loadFixtures([
            LoadFleaMarketBrandWithoutLogo::class,
        ])->getReferenceRepository();

        $this->brandToUpdate = $referenceRepository->getReference(LoadFleaMarketBrandWithoutLogo::REFERENCE_NAME);
    }

    protected function tearDown(): void
    {
        unset($this->brandToUpdate);

        parent::tearDown();
    }

    public function testNotBlankFields(): void
    {
        $command = new UpdateBrandCommand($this->brandToUpdate);

        $this->assertOnlyFieldsAreInvalid($command, ['title', 'description'], null, 'Это поле обязательно для заполнения');
    }

    public function testInvalidLengthFields(): void
    {
        $command = new UpdateBrandCommand($this->brandToUpdate);

        $longText = $this->getFaker()->realText(300);
        $command->title = $longText;
        $command->description = $longText;

        $this->getValidator()->validate($command);

        $this->assertOnlyFieldsAreInvalid($command, ['title', 'description'], $this->getFaker()->realText(300), 'Длина не должна превышать 255 символов');
    }

    public function testValidationPassedForCorrectFilledCommandWithoutImage(): void
    {
        $command = new UpdateBrandCommand($this->brandToUpdate);

        $command->title = 'Test';
        $command->description = 'Test';

        $this->getValidator()->validate($command);

        $this->assertCount(0, $this->getValidator()->getLastErrors());
    }

    public function testValidationPassedForCorrectFilledCommand(): void
    {
        $command = new UpdateBrandCommand($this->brandToUpdate);

        $command->title = 'Test';
        $command->description = 'Test';
        $command->logoImage = $this->createLogoImage();

        $this->getValidator()->validate($command);

        $this->assertCount(0, $this->getValidator()->getLastErrors());
    }

    private function createLogoImage(): UploadedFile
    {
        return new UploadedFile(
            sprintf('%s/image20x29.jpeg', $this->getDataFixturesFolder()),
            'image20x29.jpeg',
            null,
            100,
            0,
            true
        );
    }
}
