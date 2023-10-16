<?php

namespace Tests\Unit\Domain\Rss\Record\PartitionConverter;

use App\Domain\Rss\Record\PartitionConverter\Article as ArticleConverter;
use App\Domain\Record\Article\Entity\Article;

/**
 * @group rss
 */
class ArticleTest extends TestCase
{
    /** @var ArticleConverter */
    private $articleConverter;

    protected function setUp(): void
    {
        parent::setUp();

        $this->articleConverter = new ArticleConverter(
            $this->createCollectionBuilderMock(),
            $this->createContentEncodedCollectionFactoryMock(),
            $this->createEnclosureChooserMock(),
            $this->createCategoryChooserMock()
        );
    }

    protected function tearDown(): void
    {
        unset($this->articleConverter);

        parent::tearDown();
    }

    public function testPreviewMustBeConvertedToItemDescription(): void
    {
        /** @var Article $article */
        $article = $this->createConfiguredMock(Article::class, [
            'getPreview' => 'Article description',
        ]);

        $this->assertEquals($article->getPreview(), $this->articleConverter->convertDescription($article));
    }

    public function testTextMustBeConvertedToItemDescriptionIfPreviewIsEmpty(): void
    {
        /** @var Article $article */
        $article = $this->createConfiguredMock(Article::class, [
            'getText' => 'Article text',
        ]);

        $this->assertEquals($article->getText(), $this->articleConverter->convertDescription($article));
    }

    public function testTextMustBeConvertedToItemContent(): void
    {
        /** @var Article $article */
        $article = $this->createConfiguredMock(Article::class, [
            'getText' => 'Article text',
        ]);

        $this->assertEquals($article->getText(), $this->articleConverter->convertContent($article));
    }

    public function testPreviewMustBeConvertedToItemContentIfTextIsEmpty(): void
    {
        /** @var Article $article */
        $article = $this->createConfiguredMock(Article::class, [
            'getPreview' => 'Article preview',
        ]);

        $this->assertEquals($article->getPreview(), $this->articleConverter->convertContent($article));
    }
}
