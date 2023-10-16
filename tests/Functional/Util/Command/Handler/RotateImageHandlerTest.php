<?php

namespace Tests\Functional\Util\Command\Handler;

use App\Util\ImageStorage\Command\RotateImageCommand;
use App\Util\ImageStorage\Image;
use App\Util\ImageStorage\ImageStorageClientInterface;
use App\Util\ImageStorage\ImageStorageClientMock;
use Tests\Functional\TestCase;

class RotateImageHandlerTest extends TestCase
{
    private $image;

    /** @var ImageStorageClientMock */
    private $imageStorageClient;

    protected function setUp(): void
    {
        parent::setUp();

        $this->imageStorageClient = $this->getContainer()->get(ImageStorageClientInterface::class);

        $this->image = new Image('filename.png');
    }

    protected function tearDown(): void
    {
        unset($this->image, $this->imageStorageClient);

        parent::tearDown();
    }

    public function testImageNotTransform(): void
    {
        $command = new RotateImageCommand($this->image, 0);

        $accurateImage = $this->getCommandBus()->handle($command);

        $this->assertEquals($this->image, $accurateImage);

        $uploadedImages = $this->imageStorageClient->getTransformedImagesAndClear();
        $this->assertCount(0, $uploadedImages);
    }

    public function testImageTransformed(): void
    {
        $command = new RotateImageCommand($this->image, 90);

        $accurateImage = $this->getCommandBus()->handle($command);

        $this->assertEquals('transformer image name.png', (string) $accurateImage);

        $uploadedImages = $this->imageStorageClient->getTransformedImagesAndClear();

        $this->assertCount(1, $uploadedImages);

        $this->assertEquals($this->image, current($uploadedImages));
    }
}
