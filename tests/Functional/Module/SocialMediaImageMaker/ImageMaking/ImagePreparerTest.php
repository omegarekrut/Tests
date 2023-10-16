<?php

namespace Tests\Functional\Module\SocialMediaImageMaker\ImageMaking;

use App\Module\SocialMediaImageMaker\ImageMaking\ImagePreparer;
use Intervention\Image\Image;
use Tests\Functional\TestCase;

class ImagePreparerTest extends TestCase
{
    private const OPTIMAL_SIZE_WIDTH = 640;
    private const OPTIMAL_SIZE_HEIGHT = 333;

    public function testCreateImageFromDefaultImageUrl(): void
    {
        /** @var ImagePreparer $imagePreparer */
        $imagePreparer = $this->getContainer()->get(ImagePreparer::class);

        $kernelRootDir = $this->getContainer()->getParameter('kernel.root_dir');
        $socialBackgroundImageUrl = sprintf('file://%s/../www/img/og/fishingsib.jpg', $kernelRootDir);

        $createdImage = $imagePreparer->prepareImageFromUrl($socialBackgroundImageUrl, self::OPTIMAL_SIZE_WIDTH, self::OPTIMAL_SIZE_HEIGHT);

        $this->assertInstanceOf(Image::class, $createdImage);
        $this->assertEquals(self::OPTIMAL_SIZE_WIDTH, $createdImage->getWidth());
        $this->assertEquals(self::OPTIMAL_SIZE_HEIGHT, $createdImage->getHeight());
    }
}
