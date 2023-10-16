<?php

namespace Tests\Functional\Domain\User\Command\Avatar;

use App\Domain\User\Command\Avatar\UploadAvatarCommand;
use App\Domain\User\Entity\User;
use App\Util\ImageStorage\TransferObject\CroppableImageTransferObject;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Tests\Functional\ValidationTestCase;

class UploadAvatarCommandValidationTest extends ValidationTestCase
{
    private UploadAvatarCommand $command;

    protected function setUp(): void
    {
        parent::setUp();

        $this->command = new UploadAvatarCommand($this->createMock(User::class));
    }

    protected function tearDown(): void
    {
        unset($this->command);

        parent::tearDown();
    }

    public function testCroppableImageMustBeValid(): void
    {
        $invalidCroppableImage = new CroppableImageTransferObject();
        $this->command->croppableImage = $invalidCroppableImage;

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
