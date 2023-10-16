<?php

namespace Tests\Functional\Util\ImageStorage\Command\Handler;

use App\Util\ImageStorage\Command\RemoveImageFromStorageCommand;
use App\Util\ImageStorage\Image;
use App\Util\ImageStorage\ImageStorageClientInterface;
use App\Util\ImageStorage\ImageStorageClientMock;
use Tests\Functional\TestCase;

class RemoveImageFromStorageHandlerTest extends TestCase
{
    private ImageStorageClientMock $imageStorageClient;
    private RemoveImageFromStorageCommand $command;

    protected function setUp(): void
    {
        parent::setUp();

        $image = new Image(sprintf('%simage20x29.jpeg', $this->getDataFixturesFolder()));

        $this->command = new RemoveImageFromStorageCommand($image);
        $this->imageStorageClient = $this->getContainer()->get(ImageStorageClientInterface::class);
    }

    protected function tearDown(): void
    {
        unset(
            $this->imageStorageClient,
            $this->command
        );

        parent::tearDown();
    }

    public function testOldCompanyLogoImageMustBeDeletedInStorage(): void
    {
        $oldImageSource = $this->command->getImage();

        $this->getCommandBus()->handle($this->command);

        $deleteImages = $this->imageStorageClient->getDeletedImagesAndClear();

        $this->assertCount(1, $deleteImages);

        $deleteImage = current($deleteImages);

        $this->assertEquals($oldImageSource, $deleteImage);
    }
}
