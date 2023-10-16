<?php

namespace Tests\Functional\Util\ImageStorage\Command\Handler;

use App\Util\ImageStorage\Command\ImageCroppingCommand;
use App\Util\ImageStorage\ImageCropping;
use App\Util\ImageStorage\TransferObject\CroppableImageTransferObject;
use App\Util\ImageStorage\ValueObject\ImageCroppingParameters;
use App\Util\ImageStorage\ImageStorageClientInterface;
use App\Util\ImageStorage\ImageStorageClientMock;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Tests\Functional\TestCase;

class ImageCroppingHandlerTest extends TestCase
{
    private ImageStorageClientMock $imageStorageClient;
    private ImageCroppingCommand $command;

    protected function setUp(): void
    {
        parent::setUp();

        $this->imageStorageClient = $this->getContainer()->get(ImageStorageClientInterface::class);

        $croppableImage = new CroppableImageTransferObject();
        $croppableImage->imageFile = new UploadedFile(
            sprintf('%s/image20x29.jpeg', $this->getDataFixturesFolder()),
            'image20x29.jpeg',
            null,
            100,
            0,
            true
        );
        $croppableImage->sourceImageWidth = 20;
        $croppableImage->rotationAngle = 0;
        $croppableImage->croppingParameters = ['x0' => 0, 'y0' => 0, 'x1' => 10, 'y1' => 10];

        $this->command = new ImageCroppingCommand($croppableImage);
    }

    protected function tearDown(): void
    {
        unset(
            $this->imageStorageClient,
            $this->command
        );

        parent::tearDown();
    }

    public function testAfterHandlingImageCropping(): void
    {
        /** @var ImageCropping $imageCropping */
        $imageCropping = $this->getCommandBus()->handle($this->command);

        $this->assertNotEmpty($imageCropping->getImage());
        $this->assertNotEmpty($imageCropping->getCroppingParameters());
    }

    public function testImageMustBeUploadedToImageStorage(): void
    {
        $this->getCommandBus()->handle($this->command);

        $this->assertContains($this->command->croppableImage->imageFile, $this->imageStorageClient->getUploadedImageFileInformationAndClear());
    }

    public function testImageMustBeTransformedOnImageStorageIfRotationRequired(): void
    {
        $this->command->croppableImage->rotationAngle = 90;

        $this->getCommandBus()->handle($this->command);

        $this->assertCount(1, $this->imageStorageClient->getTransformedImagesAndClear());
    }

    public function testImageMustNotBeTransformedOnImageStorageIfRotationNotRequired(): void
    {
        $this->getCommandBus()->handle($this->command);

        $this->assertCount(0, $this->imageStorageClient->getTransformedImagesAndClear());
    }

    public function testCroppingParametersMustBeRecalculatedIfImageSizeChangesInStorage(): void
    {
        $imageReductionFactorOnImageStorage = 2;
        $sourceImageWidth = $this->command->croppableImage->sourceImageWidth * $imageReductionFactorOnImageStorage;

        $this->command->croppableImage->sourceImageWidth = $sourceImageWidth;

        /** @var ImageCroppingParameters $commandCroppingParameters */
        $commandCroppingParameters = ImageCroppingParameters::createByArray($this->command->croppableImage->croppingParameters);
        $expectedRecalculatedParameters = new ImageCroppingParameters(
            $commandCroppingParameters->getX0() / $imageReductionFactorOnImageStorage,
            $commandCroppingParameters->getY0() / $imageReductionFactorOnImageStorage,
            $commandCroppingParameters->getX1() / $imageReductionFactorOnImageStorage,
            $commandCroppingParameters->getY1() / $imageReductionFactorOnImageStorage
        );

        /** @var ImageCropping $imageCropping */
        $imageCropping = $this->getCommandBus()->handle($this->command);

        $this->assertNotEquals($commandCroppingParameters, $imageCropping->getCroppingParameters());
        $this->assertEquals($expectedRecalculatedParameters, $imageCropping->getCroppingParameters());
    }
}
