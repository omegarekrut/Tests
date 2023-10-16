<?php

namespace Tests\Functional\Domain\Record\News\View;

use App\Domain\Author\View\AuthorView;
use App\Domain\Comment\Collection\CommentCollection;
use App\Domain\Hashtag\Collection\HashtagCollection;
use App\Domain\Hashtag\Entity\Hashtag;
use App\Domain\Hashtag\Parser\HashtagParser;
use App\Domain\Record\Common\View\RecordViewCommonInformationFiller;
use App\Domain\Record\Common\View\RecordViewUrlGenerator;
use App\Domain\Record\News\Entity\News;
use App\Domain\Record\News\View\NewsViewFactory;
use App\Module\Voting\Entity\VotableIdentifier;
use App\Twig\Hashtag\HashtagLinkerFilter;
use App\Util\ImageStorage\Image;
use App\Util\ImageStorage\ImageTransformerFactory;
use App\Util\StringFilter\CleanedTextLineFilter;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Tests\DataFixtures\ORM\LoadHashtags;
use Tests\DataFixtures\ORM\Record\LoadNews;
use Tests\Functional\TestCase;

/**
 * @group record-view
 */
class NewsViewFactoryTest extends TestCase
{
    private const NEWS_URL = 'some/url';

    private News $news;
    private NewsViewFactory $newsViewFactory;
    private RecordViewUrlGenerator $recordViewUrlGenerator;
    private CleanedTextLineFilter $cleanedTextLineFilter;
    private ImageTransformerFactory $imageTransformerFactory;
    private Hashtag $hashtag;
    private HashtagParser $hashtagParser;

    protected function setUp(): void
    {
        parent::setUp();

        $referenceRepository = $this->loadFixtures([
            LoadNews::class,
            LoadHashtags::class,
        ])->getReferenceRepository();

        $this->news = $referenceRepository->getReference(LoadNews::getRandReferenceName());
        $this->hashtag = $referenceRepository->getReference(LoadHashtags::getReferenceNameBySlug(LoadHashtags::HASHTAG_SLUG_WINTER_FISHING));
        $this->recordViewUrlGenerator = $this->getContainer()->get(RecordViewUrlGenerator::class);
        $this->newsViewFactory = $this->createNewsViewFactory();
        $this->cleanedTextLineFilter = $this->getContainer()->get(CleanedTextLineFilter::class);
        $this->imageTransformerFactory = $this->getContainer()->get(ImageTransformerFactory::class);
        $this->hashtagParser = $this->getContainer()->get(HashtagParser::class);
    }

    protected function tearDown(): void
    {
        unset(
            $this->news,
            $this->hashtag,
            $this->newsViewFactory,
            $this->recordViewUrlGenerator,
            $this->cleanedTextLineFilter,
            $this->imageTransformerFactory,
            $this->hashtagParser
        );

        parent::tearDown();
    }

    public function testMetadataTitleShouldBeEqualsNewsTitle(): void
    {
        $newsView = $this->newsViewFactory->create($this->news);

        $this->assertEquals($this->news->getTitle(), $newsView->metadata->title);
    }

    public function testHeadingShouldBeEqualsNewsTitle(): void
    {
        $newsView = $this->newsViewFactory->create($this->news);

        $this->assertEquals($this->news->getTitle(), $newsView->heading);
    }

    public function testMetadataViewUrlShouldLeadToPageForViewingNews(): void
    {
        $newsView = $this->newsViewFactory->create($this->news);
        $viewNewsPageUrl = $this->recordViewUrlGenerator->generate($this->news);

        $this->assertEquals($viewNewsPageUrl, (string) $newsView->metadata->viewUrl);
    }

    public function testMetadataDescriptionShouldBeCreatedFromNewsPreview(): void
    {
        $news = $this->createNewsWithPreview('simple text');
        $newsView = $this->newsViewFactory->create($news);

        $this->assertEquals($news->getPreview(), $newsView->metadata->description);
    }

    public function testPreviewShouldBeCreatedFromNewsPreview(): void
    {
        $news = $this->createNewsWithPreview('simple text');
        $newsView = $this->newsViewFactory->create($news);

        $this->assertEquals($news->getPreview(), $newsView->previewText);
    }

    public function testDescriptionAndPreviewShouldClearedByCleanedTextLineFilter(): void
    {
        $news = $this->createNewsWithPreview("not prepared \n\n<b>preview</b> text");
        $newsView = $this->newsViewFactory->create($news);

        $cleanedTextLineFilter = $this->cleanedTextLineFilter;

        $this->assertEquals($cleanedTextLineFilter($news->getPreview()), $newsView->metadata->description);
        $this->assertEquals($cleanedTextLineFilter($news->getPreview()), $newsView->previewText);
    }

    public function testHtmlTextShouldBeCreatedFromNewsText(): void
    {
        $news = $this->createNewsWithText('simple text');
        $newsView = $this->newsViewFactory->create($news);

        $this->assertStringContainsString($news->getText(), $newsView->htmlText);
    }

    public function testNewsHtmlTextShouldNotContainsEscapedCharacter(): void
    {
        $news = $this->createNewsWithText('\\"');
        $newsView = $this->newsViewFactory->create($news);

        $expectedText = '"';

        $this->assertStringContainsString($expectedText, $newsView->htmlText);
    }

    public function testTagsInHtmlTextShouldBeMarketAndAddedListTagList(): void
    {
        $news = $this->createNewsWithText('#'.$this->hashtag->getName());
        $newsView = $this->newsViewFactory->create($news);

        $expectedHtmlText = $this->hashtagParser->addLinksToHashtags($news->getText(), new HashtagCollection([$this->hashtag]));

        $this->assertStringContainsString($expectedHtmlText, $newsView->htmlText);
    }

    public function testHtmlTextShouldBeCreatedFromNewsPreviewIfTextIsEmpty(): void
    {
        $news = $this->createNewsWithPreview('preview');
        $newsView = $this->newsViewFactory->create($news);

        $this->assertStringContainsString($news->getPreview(), $newsView->htmlText);
    }

    public function testHtmlTextShouldNotContainsHtmlSpecialCharsFromSource(): void
    {
        $news = $this->createNewsWithPreview('<p>content</p>');
        $newsView = $this->newsViewFactory->create($news);

        $expectedText = htmlspecialchars($news->getPreview());

        $this->assertStringContainsString($expectedText, $newsView->htmlText);
    }

    public function testLineBreaksInNewsHtmlFromPreviewShouldBePreparedLikeBrTags(): void
    {
        $news = $this->createNewsWithPreview("First line\nSecond line");
        $newsView = $this->newsViewFactory->create($news);

        $expectedHtmlText = "First line<br />\nSecond line";

        $this->assertStringContainsString($expectedHtmlText, $newsView->htmlText);
    }

    public function testUrlsMustBeLinksInNewsHtmlFromPreview(): void
    {
        $news = $this->createNewsWithPreview('http://foo.bar');
        $newsView = $this->newsViewFactory->create($news);

        $expectedHtmlText = '<a href="http://foo.bar" target="_blank" rel="nofollow">http://foo.bar</a>';

        $this->assertStringContainsString($expectedHtmlText, $newsView->htmlText);
    }

    public function testMapAndOtherScriptsWithPredefinedWidthShouldLoseWithDefinitionInViewText(): void
    {
        $news = $this->createNewsWithText('
            <script type="text/javascript" charset="utf-8" async src="https://some.map/?width=1000&amp;height=500"></script>
        ');

        $newsView = $this->newsViewFactory->create($news);

        $this->assertStringContainsString(
            '<script type="text/javascript" charset="utf-8" async src="https://some.map/?height=500"></script>',
            $newsView->htmlText
        );
    }

    public function testViewImageMustBeEqualsNewsImageTransformer(): void
    {
        $newsView = $this->newsViewFactory->create($this->news);
        $expectedImage = $this->imageTransformerFactory->create($this->news->getImage());

        $this->assertEquals($expectedImage, $newsView->image);
    }

    private function createNewsWithPreview(string $preview): News
    {
        $stub = $this->createMock(News::class);
        $stub
            ->method('getPreview')
            ->willReturn($preview);
        $stub
            ->method('getImage')
            ->willReturn(new Image('some-image.jpg'));
        $stub
            ->method('getVotableId')
            ->willReturn(new VotableIdentifier('1', 'type'));
        $stub
            ->method('getComments')
            ->willReturn(new CommentCollection());
        $stub
            ->method('getCommentsWithAnswers')
            ->willReturn(new CommentCollection());

        return $stub;
    }

    private function createNewsWithText(string $text): News
    {
        $stub = $this->createMock(News::class);
        $stub
            ->method('getText')
            ->willReturn($text);
        $stub
            ->method('getImage')
            ->willReturn(new Image('some-image.jpg'));
        $stub
            ->method('getVotableId')
            ->willReturn(new VotableIdentifier('1', 'type'));
        $stub
            ->method('getComments')
            ->willReturn(new CommentCollection());
        $stub
            ->method('getCommentsWithAnswers')
            ->willReturn(new CommentCollection());

        return $stub;
    }

    private function createNewsViewFactory(): NewsViewFactory
    {
        return new NewsViewFactory(
            $this->getContainer()->get(CleanedTextLineFilter::class),
            $this->getContainer()->get(ImageTransformerFactory::class),
            $this->recordViewUrlGenerator,
            $this->getContainer()->get(HashtagLinkerFilter::class),
            $this->createMock(RecordViewCommonInformationFiller::class)
        );
    }
}
