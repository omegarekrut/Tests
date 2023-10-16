<?php

namespace Tests\Functional\Util\Command\Handler;

use App\Util\ImageStorage\Exception\UploadImageByUrlException;
use App\Util\ImageStorage\Command\UploadImageByUrlCommand;
use App\Util\ImageStorage\ImageStorageClientInterface;
use App\Util\ImageStorage\ImageStorageClientMock;
use Tests\Functional\TestCase;

class UploadImageByUrlHandlerTest extends TestCase
{
    /** @var \SplFileInfo */
    private $imageUrl;

    /** @var ImageStorageClientMock */
    private $imageStorageClient;

    protected function setUp(): void
    {
        parent::setUp();

        $this->imageUrl = sprintf('%s/image20x29.jpeg', $this->getDataFixturesFolder()); // Instead fake url uses file name for speedup the test
        $this->imageStorageClient = $this->getContainer()->get(ImageStorageClientInterface::class);
    }

    protected function tearDown(): void
    {
        unset($this->imageUrl, $this->imageStorageClient);

        parent::tearDown();
    }

    public function testImageCanBeUploadedToImageStorage(): void
    {
        $command = new UploadImageByUrlCommand($this->imageUrl);

        $this->getCommandBus()->handle($command);

        $uploadedImages = $this->imageStorageClient->getUploadedImageFileInformationAndClear();

        $this->assertCount(1, $uploadedImages);

        $uploadedImage = current($uploadedImages);
        assert($uploadedImage instanceof \SplFileInfo);

        $this->assertEquals(filesize($this->imageUrl), $uploadedImage->getSize());
    }

    public function testImageUploadingMustFailForNotDownloadableImage(): void
    {
        $this->expectException(UploadImageByUrlException::class);

        $command = new UploadImageByUrlCommand('not downloadable image url');

        $this->getCommandBus()->handle($command);
    }
}
