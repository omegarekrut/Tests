<?php

namespace Tests\Functional\Domain\FleaMarket\Command\Brand;

use App\Domain\FleaMarket\Command\Brand\CreateBrandCommand;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Tests\Functional\ValidationTestCase;

class CreateBrandCommandValidationTest extends ValidationTestCase
{
    private CreateBrandCommand $command;

    protected function setUp(): void
    {
        parent::setUp();

        $this->command = new CreateBrandCommand();
    }

    protected function tearDown(): void
    {
        unset($this->command);

        parent::tearDown();
    }

    public function testNotBlankFields(): void
    {
        $this->assertOnlyFieldsAreInvalid($this->command, ['title', 'description'], null, 'Это поле обязательно для заполнения');
    }

    public function testInvalidLengthFields(): void
    {
        $this->assertOnlyFieldsAreInvalid($this->command, ['title', 'description'], $this->getFaker()->realText(300), 'Длина не должна превышать 255 символов');
    }

    public function testValidationPassedForCorrectFilledCommandWithoutImage(): void
    {
        $this->command->title = 'Test';
        $this->command->description = 'Test';

        $this->getValidator()->validate($this->command);

        $this->assertCount(0, $this->getValidator()->getLastErrors());
    }

    public function testValidationPassedForCorrectFilledCommand(): void
    {
        $this->command->title = 'Test';
        $this->command->description = 'Test';
        $this->command->logoImage = $this->createLogoImage();

        $this->getValidator()->validate($this->command);

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
