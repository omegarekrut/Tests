<?php

namespace Tests\Unit\Domain\Rss\Record\PartitionConverter;

use App\Domain\Category\Entity\Category;
use App\Domain\Record\Gallery\Entity\Gallery;
use App\Domain\Rss\Record\PartitionConverter\Gallery as GalleryConverter;
use App\Module\Author\AuthorInterface;
use App\Util\ImageStorage\Image;
use DateTime;

/**
 * @group rss
 */
class GalleryTest extends TestCase
{
    /** @var GalleryConverter */
    private $galleryConverter;

    protected function setUp(): void
    {
        parent::setUp();

        $this->galleryConverter = new GalleryConverter(
            $this->createCollectionBuilderMock(),
            $this->createContentEncodedCollectionFactoryMock(),
            $this->createEnclosureChooserMock(),
            $this->createUrlGeneratorMock(),
            $this->createCategoryChooserMock()
        );
    }

    protected function tearDown(): void
    {
        unset($this->galleryConverter);

        parent::tearDown();
    }

    public function testContentMustContainsTextAndImage(): void
    {
        /** @var Gallery $gallery */
        $gallery = $this->createConfiguredMock(Gallery::class, [
            'getText' => 'Gallery text',
            'getImage' => new Image('image1.jpg'),
        ]);

        $content = $this->galleryConverter->convertContent($gallery);

        $this->assertStringContainsString($gallery->getText(), $content);
        $this->assertStringContainsString($gallery->getImage()->getFilename(), $content);
    }

    public function testContentMustContainsDescriptionIfGalleryTextIsEmpty(): void
    {
        /** @var Gallery $gallery */
        $gallery = $this->createConfiguredMock(Gallery::class, [
            'getTitle' => 'Gallery title',
            'getDescription' => 'Gallery description',
        ]);

        $expectedContent = $this->galleryConverter->convertDescription($gallery);

        $content = $this->galleryConverter->convertContent($gallery);

        $this->assertStringContainsString($expectedContent, $content);
    }

    public function testDescriptionMustContainsDescriptionAndTitle(): void
    {
        /** @var Gallery $gallery */
        $gallery = $this->createConfiguredMock(Gallery::class, [
            'getTitle' => 'Gallery title',
            'getDescription' => 'Gallery description',
        ]);

        $description = $this->galleryConverter->convertContent($gallery);

        $this->assertStringContainsString('Фотографии рыбаков: ', $description);
        $this->assertStringContainsString($gallery->getTitle(), $description);
        $this->assertStringContainsString($gallery->getDescription(), $description);
    }

    public function testDescriptionMustContainsAnotherDifferentDataIfDescriptionIsEmpty(): void
    {
        /** @var Category $category */
        $category = $this->createConfiguredMock(Category::class, [
            'getTitle' => 'Category title',
        ]);

        /** @var AuthorInterface $author */
        $author = $this->createConfiguredMock(AuthorInterface::class, [
            'getUsername' => 'Author name',
        ]);

        /** @var Gallery $gallery */
        $gallery = $this->createConfiguredMock(Gallery::class, [
            'getTitle' => 'Gallery title',
            'getCategory' => $category,
            'getAuthor' => $author,
            'getCreatedAt' => new DateTime(),
        ]);

        $description = $this->galleryConverter->convertDescription($gallery);

        $this->assertStringContainsString($gallery->getTitle(), $description);
        $this->assertStringContainsString('Фотографии рыбаков. Рубрика: ', $description);
        $this->assertStringContainsString($gallery->getCategory()->getTitle(), $description);
        $this->assertStringContainsString($gallery->getCreatedAt()->format('d.m.Y'), $description);
        $this->assertStringContainsString($gallery->getAuthor()->getUsername(), $description);
    }
}
