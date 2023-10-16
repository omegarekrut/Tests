<?php

namespace Tests\Functional\Util\ImageStorage\Command;

use App\Util\ImageStorage\Command\ImageCroppingCommand;
use App\Util\ImageStorage\TransferObject\CroppableImageTransferObject;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Tests\Functional\ValidationTestCase;

class ImageCroppingCommandValidationTest extends ValidationTestCase
{
    private ImageCroppingCommand $command;

    protected function setUp(): void
    {
        parent::setUp();

        $this->command = new ImageCroppingCommand(new CroppableImageTransferObject());
    }

    protected function tearDown(): void
    {
        unset($this->command);

        parent::tearDown();
    }

    public function testCroppableImageMustBeValid(): void
    {
        $this->getValidator()->validate($this->command);

        $this->assertGreaterThan(0, count($this->getValidator()->getLastErrors()));
    }

    public function testValidationShouldBePassedForCorrectFilledCommand(): void
    {
        $this->command->croppableImage = new CroppableImageTransferObject();
        $this->command->croppableImage->imageFile = new UploadedFile(
            sprintf('%s/image20x29.jpeg', $this->getDataFixturesFolder()),
            'image20x29.jpeg',
            null,
            100,
            0,
            true
        );
        $this->command->croppableImage->sourceImageWidth = 20;
        $this->command->croppableImage->rotationAngle = 90;
        $this->command->croppableImage->croppingParameters = [
            'x0' => 0,
            'y0' => 0,
            'x1' => 10,
            'y1' => 10,
        ];

        $this->getValidator()->validate($this->command);

        $this->assertEmpty($this->getValidator()->getLastErrors());
    }
}
