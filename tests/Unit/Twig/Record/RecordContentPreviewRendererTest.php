<?php

namespace Tests\Unit\Twig\Record;

use App\Domain\Record\Article\Entity\Article;
use App\Domain\Record\Gallery\Entity\Gallery;
use App\Domain\Record\Map\Entity\Map;
use App\Domain\Record\News\Entity\News;
use App\Domain\Record\Tackle\Entity\TackleReview;
use App\Domain\Record\Tidings\Entity\Tidings;
use App\Domain\Record\Video\Entity\Video;
use App\Twig\Record\RecordContentPreviewRenderer;
use App\Util\StringFilter\BBCode\BBCodeToHtmlFilter;
use App\Util\StringFilter\CleanedTextLineFilter;
use JBBCode\Parser;
use Tests\Unit\TestCase;

class RecordContentPreviewRendererTest extends TestCase
{
    private const TEST_TEXT = 'some description data for record.';

    private RecordContentPreviewRenderer $renderer;

    protected function setUp(): void
    {
        parent::setUp();

        $bbCodeParser = new Parser();
        $bbCodeParser->addBBCode('b', '<strong>{param}</strong>');

        $cleanedTextLineFilter = new CleanedTextLineFilter(new BBCodeToHtmlFilter($bbCodeParser));
        $this->renderer = new RecordContentPreviewRenderer($cleanedTextLineFilter);
    }

    protected function tearDown(): void
    {
        unset($this->renderer);

        parent::tearDown();
    }

    public function testPreviewRendererForTextWithBbCodes(): void
    {
        $record = $this->createConfiguredMock(Tidings::class, [
            'getText' => " prefix [b]some text[/b] \nsuffix ",
        ]);

        $previewText = ($this->renderer)($record, 100);

        $this->assertEquals('prefix some text suffix', $previewText);
    }

    public function testLengthPreviewRendererForTidingsWithText(): void
    {
        $record = $this->createConfiguredMock(Tidings::class, [
            'getText' => self::TEST_TEXT,
        ]);

        $previewText = ($this->renderer)($record, 10);

        $this->assertPreviewText($previewText, 10);
    }

    public function testLengthPreviewRendererForNewsWithPreview(): void
    {
        $record = $this->createConfiguredMock(News::class, [
            'getPreview' => self::TEST_TEXT,
        ]);

        $previewText = ($this->renderer)($record, 10);

        $this->assertPreviewText($previewText, 10);
    }

    public function testLengthPreviewRendererForNewsWithTextAndEmptyPreview(): void
    {
        $record = $this->createConfiguredMock(News::class, [
            'getPreview' => '',
            'getText' => self::TEST_TEXT,
        ]);

        $previewText = ($this->renderer)($record, 10);

        $this->assertPreviewText($previewText, 10);
    }

    public function testLengthPreviewRendererForTackleReviewWithText(): void
    {
        $record = $this->createConfiguredMock(TackleReview::class, [
            'getText' => self::TEST_TEXT,
        ]);

        $previewText = ($this->renderer)($record, 10);

        $this->assertPreviewText($previewText, 10);
    }

    public function testLengthPreviewRendererForArticleWithPreview(): void
    {
        $record = $this->createConfiguredMock(Article::class, [
            'getPreview' => self::TEST_TEXT,
        ]);

        $previewText = ($this->renderer)($record, 10);

        $this->assertPreviewText($previewText, 10);
    }

    public function testLengthPreviewRendererForArticleWithTextAndNullPreview(): void
    {
        $record = $this->createConfiguredMock(Article::class, [
            'getPreview' => null,
            'getText' => self::TEST_TEXT,
        ]);

        $previewText = ($this->renderer)($record, 10);

        $this->assertPreviewText($previewText, 10);
    }

    public function testLengthPreviewRendererForArticleWithTextAndEmptyPreview(): void
    {
        $record = $this->createConfiguredMock(Article::class, [
            'getPreview' => '',
            'getText' => self::TEST_TEXT,
        ]);

        $previewText = ($this->renderer)($record, 10);

        $this->assertPreviewText($previewText, 10);
    }

    public function testLengthPreviewRendererForGalleryWithDescription(): void
    {
        $record = $this->createConfiguredMock(Gallery::class, [
            'getDescription' => self::TEST_TEXT,
        ]);

        $previewText = ($this->renderer)($record, 10);

        $this->assertPreviewText($previewText, 10);
    }

    public function testLengthPreviewRendererForVideoWithDescription(): void
    {
        $record = $this->createConfiguredMock(Video::class, [
            'getDescription' => self::TEST_TEXT,
        ]);

        $previewText = ($this->renderer)($record, 10);

        $this->assertPreviewText($previewText, 10);
    }

    public function testLengthPreviewRendererForMapWithDescription(): void
    {
        $record = $this->createConfiguredMock(Map::class, [
            'getDescription' => self::TEST_TEXT,
        ]);

        $previewText = ($this->renderer)($record, 10);

        $this->assertPreviewText($previewText, 10);
    }

    private function assertPreviewText(string $previewText, int $maxLength): void
    {
        $this->assertLessThan($maxLength, mb_strlen($previewText));
        $this->assertNotEmpty($previewText);
    }
}
