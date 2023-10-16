<?php

namespace Tests\Unit\Twig\Record;

use App\Twig\Record\StorageImageBBCodeTagToBBCodeImagePreparer;
use App\Util\ImageStorage\ImageTransformer;
use App\Util\ImageStorage\ImageTransformerFactory;
use Tests\Unit\TestCase;

/**
 * @group twig
 */
class StorageImageBBCodeTagToBBCodeImagePreparerTest extends TestCase
{
    private const EXPECTED_IMAGE_URL_FOR_CONTENT = 'http://expected.url';

    public function testStorageImageTagsMustBeReplacedWithBBCodeImageTagsAfterPreparation(): void
    {
        $imageTransformerFactory = $this->createImageTransformerFactoryForGenerateImageWithUrl(self::EXPECTED_IMAGE_URL_FOR_CONTENT);
        $preparer = new StorageImageBBCodeTagToBBCodeImagePreparer($imageTransformerFactory);

        $actualText = $preparer->prepare('[image=some-image-file-name.jpeg]');
        $expectedText = sprintf('[img]%s[/img]', self::EXPECTED_IMAGE_URL_FOR_CONTENT);

        $this->assertEquals($expectedText, $actualText);
    }

    private function createImageTransformerFactoryForGenerateImageWithUrl(string $imageUrl): ImageTransformerFactory
    {
        $imageTransformer = $this->createMock(ImageTransformer::class);
        $imageTransformer
            ->method('withResize2Universal')
            ->willReturn($imageTransformer);
        $imageTransformer
            ->method('__toString')
            ->willReturn($imageUrl);

        $imageTransformerFactory = $this->createMock(ImageTransformerFactory::class);
        $imageTransformerFactory
            ->method('create')
            ->willReturn($imageTransformer);

        return $imageTransformerFactory;
    }
}
