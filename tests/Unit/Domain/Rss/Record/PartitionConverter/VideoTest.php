<?php

namespace Tests\Unit\Domain\Rss\Record\PartitionConverter;

use App\Domain\Category\Entity\Category;
use App\Domain\Record\Video\Entity\Video;
use App\Domain\Rss\Record\PartitionConverter\Video as VideoConverter;
use App\Module\Author\AuthorInterface;
use DateTime;

/**
 * @group rss
 */
class VideoTest extends TestCase
{
    /** @var VideoConverter */
    private $videoConverter;

    protected function setUp(): void
    {
        parent::setUp();

        $this->videoConverter = new VideoConverter(
            $this->createCollectionBuilderMock(),
            $this->createContentEncodedCollectionFactoryMock(),
            $this->createEnclosureChooserMock(),
            $this->createCategoryChooserMock()
        );
    }

    protected function tearDown(): void
    {
        unset($this->videoConverter);

        parent::tearDown();
    }

    public function testDescriptionMustContainsDifferentVideoInformation(): void
    {
        /** @var Category $category */
        $category = $this->createConfiguredMock(Category::class, [
            'getTitle' => 'Category title',
        ]);

        /** @var AuthorInterface $author */
        $author = $this->createConfiguredMock(AuthorInterface::class, [
            'getUsername' => 'author name',
        ]);

        /** @var Video $video */
        $video = $this->createConfiguredMock(Video::class, [
            'getTitle' => 'Video title',
            'getCategory' => $category,
            'getAuthor' => $author,
            'getCreatedAt' => new DateTime(),
            'getDescription' => 'Video description',
        ]);

        $description = $this->videoConverter->convertDescription($video);

        $this->assertStringContainsString('Виде', $description);
        $this->assertStringContainsString($video->getDescription(), $description);
        $this->assertStringContainsString('смотреть онлайн бесплатно', $description);
        $this->assertStringContainsString('Рубрика', $description);
        $this->assertStringContainsString($video->getCategory()->getTitle(), $description);
        $this->assertStringContainsString('Добавлено пользователем', $description);
        $this->assertStringContainsString($video->getAuthor()->getUsername(), $description);
        $this->assertStringContainsString($video->getCreatedAt()->format('Y'), $description);
    }

    public function testContentMustContainsVideoDescription(): void
    {
        /** @var Video $video */
        $video = $this->createConfiguredMock(Video::class, [
            'getDescription' => 'Video description',
        ]);

        $this->assertStringContainsString($video->getDescription(), $this->videoConverter->convertContent($video));
    }

    public function testContentMustContainsConvertedDescriptionIfVideoDescriptionIsEmpty(): void
    {
        /** @var Video $video */
        $video = $this->createMock(Video::class);

        $convertedDescription = $this->videoConverter->convertDescription($video);

        $this->assertStringContainsString($convertedDescription, $this->videoConverter->convertContent($video));
    }

    public function testContentMustContainsVideoIframe(): void
    {
        /** @var Video $video */
        $video = $this->createConfiguredMock(Video::class, [
            'getIframe' => 'Video iframe',
        ]);

        $this->assertStringContainsString($video->getIframe(), $this->videoConverter->convertContent($video));
    }
}
