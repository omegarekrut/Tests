<?php

namespace Tests\Functional\Util\ImageStorage\Command\Handler;

use App\Util\ImageStorage\Command\RotateImageCommand;
use App\Util\ImageStorage\Image;
use App\Util\ImageStorage\ImageStorageClientInterface;
use Tests\Functional\TestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class RotateImageHandlerTest extends TestCase
{
    private Image $imageFileInformation;
    private ImageStorageClientInterface $imageStorageClient;

    protected function setUp(): void
    {
        parent::setUp();

        $this->imageStorageClient = $this->getContainer()->get(ImageStorageClientInterface::class);

        $imageFile = new UploadedFile(
            sprintf('%s/image20x29.jpeg', $this->getDataFixturesFolder()),
            'image20x29.jpeg',
            null,
            100,
            0,
            true
        );

        $this->imageFileInformation = new Image($imageFile);
    }

    protected function tearDown(): void
    {
        unset(
            $this->imageStorageClient,
            $this->imageFileInformation
        );

        parent::tearDown();
    }

    public function testImageMustBeTransformedOnImageStorageIfRotationRequired(): void
    {
        $commandUsingRotation = new RotateImageCommand($this->imageFileInformation, 90);

        $this->getCommandBus()->handle($commandUsingRotation);

        $this->assertCount(1, $this->imageStorageClient->getTransformedImagesAndClear());
    }

    public function testImageMustNotBeTransformedOnImageStorageIfRotationNotRequired(): void
    {
        $commandWithNotRotation = new RotateImageCommand($this->imageFileInformation, 0);

        $this->getCommandBus()->handle($commandWithNotRotation);

        $this->assertCount(0, $this->imageStorageClient->getTransformedImagesAndClear());
    }
}
