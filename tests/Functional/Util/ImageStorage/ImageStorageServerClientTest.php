<?php

namespace Tests\Functional\Util\ImageStorage;

use App\Util\ImageStorage\ImageStorageServerClient;
use App\Util\ImageStorage\ImageTransformerFactory;
use Tests\Functional\TestCase;

/**
 * @group real-remote-service-test
 */
class ImageStorageServerClientTest extends TestCase
{
    /** @var ImageStorageServerClient */
    private $imageStorageClient;

    /** @var ImageTransformerFactory */
    private $imageTransformerFactory;

    /** @var \SplFileInfo */
    private $imageFileInformation;

    protected function setUp(): void
    {
        parent::setUp();

        $this->imageStorageClient = $this->getContainer()->get(ImageStorageServerClient::class);
        $this->imageTransformerFactory = $this->getContainer()->get(ImageTransformerFactory::class);
        $this->imageFileInformation = new \SplFileInfo(sprintf('%s/image20x29.jpeg', $this->getDataFixturesFolder()));
    }

    protected function tearDown(): void
    {
        unset($this->imageStorageClient, $this->imageTransformerFactory, $this->imageFileInformation);

        parent::tearDown();
    }

    public function testAfterUploadImageMustBeAvailableFromLink(): void
    {
        $imageInformation = $this->imageStorageClient->upload($this->imageFileInformation);

        $imageUrl = (string) $this->imageTransformerFactory->create($imageInformation->getImage());
        [$sourceImageWidth, $sourceImageHeight] = getimagesize($this->imageFileInformation->getRealPath());

        $this->assertFileIsAvailableByUrl((string) $imageUrl);
        $this->assertEquals($sourceImageWidth, $imageInformation->getSize()->getWidth());
        $this->assertEquals($sourceImageHeight, $imageInformation->getSize()->getHeight());
    }

    /**
     * We can't check file availability for the url, because image will have cached
     */
    public function testDeletionMustNotCauseException(): void
    {
        $imageInformation = $this->imageStorageClient->upload($this->imageFileInformation);
        $actualException = null;

        try {
            $this->imageStorageClient->delete($imageInformation->getImage());
        } catch (\Throwable $exception) {
            $actualException = $exception;
        }

        $this->assertEmpty($actualException);
    }

    public function testAfterTransformationImageMustBeAvailableForUrl(): void
    {
        $imageInformation = $this->imageStorageClient->upload($this->imageFileInformation);
        $transformableImage = $this->imageTransformerFactory->create($imageInformation->getImage());

        $rotatedImage = $this->imageStorageClient->transform($transformableImage->withRotate(90));

        $imageUrl = (string) $this->imageTransformerFactory->create($rotatedImage);
        [$sourceImageWidth, $sourceImageHeight] = getimagesize($imageUrl);
        [$expectedWidth, $expectedHeight] = [$sourceImageHeight, $sourceImageWidth];

        $this->assertFileIsAvailableByUrl($imageUrl);
        $this->assertEquals($expectedWidth, $imageInformation->getSize()->getWidth());
        $this->assertEquals($expectedHeight, $imageInformation->getSize()->getHeight());
    }

    private function assertFileIsAvailableByUrl(string $url): void
    {
        $headers = get_headers($url);

        $this->assertTrue(is_array($headers));
        $this->assertContains('HTTP/1.1 200 OK', $headers);
    }
}
