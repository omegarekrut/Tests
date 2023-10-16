<?php

namespace Tests\Functional\Util\Sceditor;

use App\Util\ImageStorage\Image;
use App\Util\ImageStorage\ImageTransformerFactory;
use App\Util\Sceditor\UrlsReplacement;
use Tests\Functional\TestCase;

class UrlsReplacementTest extends TestCase
{
    /** @var UrlsReplacement */
    private $urlsReplacement;
    private $imageTransformerFactory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->imageTransformerFactory = $this->getContainer()->get(ImageTransformerFactory::class);
        $this->urlsReplacement = new UrlsReplacement($this->imageTransformerFactory);
    }

    protected function tearDown(): void
    {
        unset(
            $this->urlsReplacement,
            $this->imageTransformerFactory
        );

        parent::tearDown();
    }

    public function testReplaceImageUrlsInTextByImages(): void
    {
        $imageFirst = new Image('filenameFirst.jpg');
        $imageSecond = new Image('filenameSecond.jpg');
        $imageThird = new Image('filenameThird.jpg');

        $images = [$imageFirst, $imageSecond];
        $rotatedImages = [$imageThird, $imageFirst];

        $textTemplate = 'some text, %s , beetween images, %s final text';

        $text = sprintf(
            $textTemplate,
            $this->createImageUrlWithResize2Universal($imageFirst, 888, 888),
            $this->createImageUrlWithResize2Universal($imageSecond, 888, 888)
        );

        $this->assertEquals(
            sprintf(
                $textTemplate,
                $this->createImageUrlWithResize2Universal($imageThird),
                $this->createImageUrlWithResize2Universal($imageFirst)
            ),
            $this->urlsReplacement->replaceImageUrlsInTextByImages($images, $rotatedImages, $text)
        );
    }

    public function testInvalidArguments(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->urlsReplacement->replaceImageUrlsInTextByImages(
            [new Image('filenameFirst.jpg')],
            [new Image('filenameFirst.jpg'), new Image('filenameSecond.jpg')],
            'some text'
        );
    }

    private function createImageUrlWithResize2Universal(Image $image, int $width = 1024, int $height = 800): string
    {
        return (string) $this->imageTransformerFactory
            ->create($image)
            ->withResize2Universal($width, $height);
    }
}
