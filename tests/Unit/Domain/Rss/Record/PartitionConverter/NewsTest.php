<?php

namespace Tests\Unit\Domain\Rss\Record\PartitionConverter;

use App\Domain\Record\News\Entity\News;
use App\Domain\Rss\Record\PartitionConverter\News as NewsConverter;

/**
 * @group rss
 */
class NewsTest extends TestCase
{
    /** @var NewsConverter */
    private $newsConverter;

    protected function setUp(): void
    {
        parent::setUp();

        $this->newsConverter = new NewsConverter(
            $this->createContentEncodedCollectionFactoryMock(),
            $this->createCollectionBuilderMock(),
            $this->createEnclosureChooserMock(),
            $this->createCategoryChooserMock()
        );
    }

    protected function tearDown(): void
    {
        unset($this->newsConverter);

        parent::tearDown();
    }

    public function testNewsTextMustBeConvertedAsItemContent(): void
    {
        /** @var News $news */
        $news = $this->createConfiguredMock(News::class, [
            'getText' => 'News text',
        ]);

        $this->assertEquals($news->getText(), $this->newsConverter->convertContent($news));
    }

    public function testNewsPreviewMustBeConvertedAsItemContentIfTextIsEmpty(): void
    {
        /** @var News $news */
        $news = $this->createConfiguredMock(News::class, [
            'getPreview' => 'News preview',
        ]);

        $this->assertEquals($news->getPreview(), $this->newsConverter->convertContent($news));
    }

    public function testNewPreviewMustBeConvertedAsItemDescription(): void
    {
        /** @var News $news */
        $news = $this->createConfiguredMock(News::class, [
            'getPreview' => 'News preview',
        ]);

        $this->assertEquals($news->getPreview(), $this->newsConverter->convertDescription($news));
    }
}
