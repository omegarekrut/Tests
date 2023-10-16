<?php

namespace Tests\Functional\Domain\Record\Article\View;

use App\Domain\Comment\Collection\CommentCollection;
use App\Domain\Hashtag\Collection\HashtagCollection;
use App\Domain\Hashtag\Entity\Hashtag;
use App\Domain\Hashtag\Parser\HashtagParser;
use App\Domain\Record\Article\Entity\Article;
use App\Domain\Record\Article\View\ArticleViewFactory;
use App\Domain\Record\Common\Collection\RecordSemanticLinkCollection;
use App\Domain\Record\Common\Entity\RecordSemanticLink;
use App\Domain\Record\Common\View\RecordViewUrlGenerator;
use App\Domain\SemanticLink\Entity\SemanticLink;
use App\Module\Voting\Entity\VotableIdentifier;
use App\Util\ImageStorage\Collection\ImageCollection;
use App\Util\ImageStorage\Image;
use App\Util\ImageStorage\ImageTransformerFactory;
use App\Util\StringFilter\CleanedTextLineFilter;
use Ramsey\Uuid\Uuid;
use Tests\DataFixtures\ORM\LoadHashtags;
use Tests\DataFixtures\ORM\Record\LoadArticles;
use Tests\DataFixtures\ORM\SemanticLink\LoadSemanticLinkWithValidUri;
use Tests\Functional\TestCase;
use Laminas\Diactoros\Uri;

/**
 * @group record-view
 */
class ArticleViewFactoryTest extends TestCase
{
    private Article $article;
    private ArticleViewFactory $articleViewFactory;
    private RecordViewUrlGenerator $recordViewUrlGenerator;
    private CleanedTextLineFilter $cleanedTextLineFilter;
    private ImageTransformerFactory $imageTransformerFactory;
    private Hashtag $hashtag;
    private HashtagParser $hashtagParser;
    private SemanticLink $semanticLink;

    protected function setUp(): void
    {
        parent::setUp();

        $referenceRepository = $this->loadFixtures([
            LoadArticles::class,
            LoadHashtags::class,
            LoadSemanticLinkWithValidUri::class,
        ])->getReferenceRepository();

        $this->article = $referenceRepository->getReference(LoadArticles::getRandReferenceName());
        $this->hashtag = $referenceRepository->getReference(LoadHashtags::getReferenceNameBySlug(LoadHashtags::HASHTAG_SLUG_WINTER_FISHING));
        $this->semanticLink = $referenceRepository->getReference(LoadSemanticLinkWithValidUri::REFERENCE_NAME);
        $this->recordViewUrlGenerator = $this->getContainer()->get(RecordViewUrlGenerator::class);
        $this->cleanedTextLineFilter = $this->getContainer()->get(CleanedTextLineFilter::class);
        $this->imageTransformerFactory = $this->getContainer()->get(ImageTransformerFactory::class);
        $this->hashtagParser = $this->getContainer()->get(HashtagParser::class);

        $this->articleViewFactory = $this->getContainer()->get(ArticleViewFactory::class);
    }

    protected function tearDown(): void
    {
        unset(
            $this->article,
            $this->hashtag,
            $this->articleViewFactory,
            $this->recordViewUrlGenerator,
            $this->cleanedTextLineFilter,
            $this->imageTransformerFactory,
            $this->hashtagParser
        );

        parent::tearDown();
    }

    public function testCommonViewInformationShouldBeEqualsCommonRecordInfo(): void
    {
        $articleView = $this->articleViewFactory->create($this->article);

        $this->assertEquals($this->article->getId(), $articleView->id);
        $this->assertEquals($this->article->getCategory(), $articleView->category);

        $this->assertEquals($this->article->getAuthor()->getId(), $articleView->author->id);
        $this->assertEquals($this->article->getAuthor()->getUsername(), $articleView->author->name);
        $this->assertEquals($this->article->getAuthor()->getSubscribers(), $articleView->author->subscribers);

        $this->assertEquals($this->article->getRatingInfo(), $articleView->ratingInfo);
        $this->assertEquals($this->article->getVotableId(), $articleView->votableId);
        $this->assertEquals($this->article->getPriority(), $articleView->priority);
        $this->assertEquals($this->article->getViews(), $articleView->views);
        $this->assertEquals($this->article->areCommentsDisplayed(), $articleView->areCommentsDisplayed);
        $this->assertEquals($this->article->areCommentsOnlyRead(), $articleView->areCommentsOnlyRead);
        $this->assertEquals($this->article->areCommentsDisallowed(), $articleView->areCommentsDisallowed);
        $this->assertEquals($this->article->getCommentsCount(), $articleView->commentsCount);
        $this->assertEquals($this->article->getCreatedAt(), $articleView->createdAt);
    }

    public function testMetadataTitleShouldBeEqualsArticleTitle(): void
    {
        $articleView = $this->articleViewFactory->create($this->article);

        $this->assertEquals($this->article->getTitle(), $articleView->metadata->title);
    }

    public function testHeadingShouldBeEqualsArticleTitle(): void
    {
        $articleView = $this->articleViewFactory->create($this->article);

        $this->assertEquals($this->article->getTitle(), $articleView->heading);
    }

    public function testMetadataViewUrlShouldLeadToPageForViewingArticle(): void
    {
        $articleView = $this->articleViewFactory->create($this->article);
        $viewArticlePageUrl = $this->recordViewUrlGenerator->generate($this->article);

        $this->assertEquals($viewArticlePageUrl, (string) $articleView->metadata->viewUrl);
    }

    public function testMetadataDescriptionAndPreviewShouldBeCreatedFromArticlePreview(): void
    {
        $article = $this->createArticleWithPreview('simple preview');
        $articleView = $this->articleViewFactory->create($article);

        $this->assertEquals($article->getPreview(), $articleView->metadata->description);
        $this->assertEquals($article->getPreview(), $articleView->previewText);
    }

    public function testMetadataDescriptionAndPreviewShouldBeCreatedFromArticleTextIfPreviewIsEmpty(): void
    {
        $article = $this->createArticleWithBBcodeText('simple text');
        $articleView = $this->articleViewFactory->create($article);

        $this->assertEquals($article->getText(), $articleView->metadata->description);
        $this->assertEquals($article->getText(), $articleView->previewText);
    }

    public function testDescriptionAndPreviewShouldClearedByCleanedTextLineFilter(): void
    {
        $article = $this->createArticleWithPreview("not prepared \n\n<b>preview</b> text");
        $articleView = $this->articleViewFactory->create($article);

        $cleanedTextLineFilter = $this->cleanedTextLineFilter;

        $this->assertEquals($cleanedTextLineFilter($article->getPreview()), $articleView->metadata->description);
        $this->assertEquals($cleanedTextLineFilter($article->getPreview()), $articleView->previewText);
    }

    public function testHtmlTextShouldBeCreatedFromArticleText(): void
    {
        $article = $this->createArticleWithBBcodeText('simple text');
        $articleView = $this->articleViewFactory->create($article);

        $this->assertStringContainsString($article->getText(), $articleView->htmlText);
    }

    public function testArticleNotHtmlShouldNotContainsExtraLineBreaksBetweenConvertedBBCodes(): void
    {
        $article = $this->createArticleWithBBcodeText("[ul][li]item[/li][/ul]\n[ul][li]item[/li][/ul]");
        $articleView = $this->articleViewFactory->create($article);

        $this->assertStringContainsString('<ul><li>item</li></ul><ul><li>item</li></ul>', $articleView->htmlText);
    }

    public function testArticleNotHtmlShouldNotContainsHtmlSpecialChars(): void
    {
        $article = $this->createArticleWithBBcodeText('<p>content</p>');
        $articleView = $this->articleViewFactory->create($article);

        $expectedText = htmlspecialchars($article->getText());

        $this->assertStringContainsString($expectedText, $articleView->htmlText);
    }

    public function testLineBreaksInArticleNotHtmlShouldBePreparedLikeParagraphTags(): void
    {
        $article = $this->createArticleWithBBcodeText("First line\nSecond line");
        $articleView = $this->articleViewFactory->create($article);

        $expectedHtmlText = '<p>First line</p><p>Second line</p>';

        $this->assertStringContainsString($expectedHtmlText, $articleView->htmlText);
    }

    public function testUrlsMustBeLinksInArticleNotHtml(): void
    {
        $article = $this->createArticleWithBBcodeText('http://foo.bar');
        $articleView = $this->articleViewFactory->create($article);

        $expectedHtmlText = '<a href="http://foo.bar" target="_blank" rel="nofollow">http://foo.bar</a>';

        $this->assertStringContainsString($expectedHtmlText, $articleView->htmlText);
    }

    public function testStorageImagesTagsMustBePreparedAsImagesTagsInHtmlText(): void
    {
        $storageImageFileName = 'filename.jpeg';
        $storageUrl = (string) $this->imageTransformerFactory
            ->create(new Image($storageImageFileName))
            ->withResize2Universal(1000, 800);

        $storageUrl = (new Uri($storageUrl))->withScheme('');

        $article = $this->createArticleWithBBcodeText("[image=$storageImageFileName]");
        $articleView = $this->articleViewFactory->create($article);

        $expectedHtmlText = "<img src=\"$storageUrl\"";

        $this->assertStringContainsString($expectedHtmlText, $articleView->htmlText);
    }

    public function testValidBBCodeMustBePreparedAsHtmlInHtmlText(): void
    {
        $article = $this->createArticleWithBBcodeText('[b]valid bbcode[/b][invalid]invalid bbcode[invalid]');
        $articleView = $this->articleViewFactory->create($article);

        $expectedHtmlText = '<b>valid bbcode</b>invalid bbcode';

        $this->assertStringContainsString($expectedHtmlText, $articleView->htmlText);
    }

    public function testTagsInHtmlTextShouldBeMarketAndAddedListTagList(): void
    {
        $article = $this->createArticleWithBBcodeText('#'.$this->hashtag->getName());
        $articleView = $this->articleViewFactory->create($article);

        $expectedHtmlText = $this->hashtagParser->addLinksToHashtags($article->getText(), new HashtagCollection([$this->hashtag]));

        $this->assertStringContainsString($expectedHtmlText, $articleView->htmlText);
    }

    public function tesHeadersInHtmlTextShouldGetIdentifiers(): void
    {
        $article = $this->createArticleWithBBcodeText('[h2]first head[h2][h2]second head[h2]');
        $articleView = $this->articleViewFactory->create($article);

        $expectedHtmlText = '<h2 id="article-contents-header-0">first head</h2><h2 id="article-contents-header-1">second head</h2>';

        $this->assertEquals($expectedHtmlText, $articleView->htmlText);
    }

    public function testMapAndOtherScriptsWithPredefinedWidthShouldLoseWithDefinitionInViewText(): void
    {
        $article = $this->createArticleWithHtmlText('
            <script type="text/javascript" charset="utf-8" async src="https://some.map/?width=1000&amp;height=500"></script>
        ');

        $articleView = $this->articleViewFactory->create($article);

        $this->assertStringContainsString(
            '<script type="text/javascript" charset="utf-8" async src="https://some.map/?height=500"></script>',
            $articleView->htmlText
        );
    }

    public function testSemanticLinksToViewTextInjector(): void
    {
        $article = $this->createArticleWithSemanticLinks('Lorem ipsum dolor sit black hole hyper отзыв amet, consectetur adipiscing elit.', [$this->semanticLink]);

        $articleView = $this->articleViewFactory->create($article);

        $this->assertStringContainsString(
            'Lorem ipsum dolor sit <a href="/articles/view/86281/">black hole hyper отзыв</a> amet, consectetur adipiscing elit.',
            $articleView->htmlText
        );
    }

    public function testViewImageMustBeEqualsArticleImageTransformers(): void
    {
        $articleView = $this->articleViewFactory->create($this->article);
        $expectedImages = $this->imageTransformerFactory->createByCollection($this->article->getImages());

        $this->assertEquals($expectedImages, $articleView->images);
    }

    public function testForArticleWithAllowedHtmlGalleryOfUnusedImagesInTextProhibited(): void
    {
        $articleView = $this->articleViewFactory->create($this->article);

        $this->assertEquals($this->article->isHtmlAllowed(), !$articleView->isAllowedGalleryUnusedImagesInText);
    }

    private function createArticleWithPreview(string $preview): Article
    {
        $stub = $this->createMock(Article::class);
        $stub
            ->method('getPreview')
            ->willReturn($preview);
        $stub
            ->method('getImages')
            ->willReturn(new ImageCollection());
        $stub
            ->method('getVotableId')
            ->willReturn(new VotableIdentifier('1', 'type'));
        $stub
            ->method('getRecordSemanticLinks')
            ->willReturn(new RecordSemanticLinkCollection());
        $stub
            ->method('getComments')
            ->willReturn(new CommentCollection());
        $stub
            ->method('getCommentsWithAnswers')
            ->willReturn(new CommentCollection());

        return $stub;
    }

    private function createArticleWithBBcodeText(string $text): Article
    {
        $stub = $this->createMock(Article::class);
        $stub
            ->method('getText')
            ->willReturn($text);
        $stub
            ->method('getImages')
            ->willReturn(new ImageCollection());
        $stub
            ->method('isHtmlAllowed')
            ->willReturn(false);
        $stub
            ->method('getVotableId')
            ->willReturn(new VotableIdentifier('1', 'type'));
        $stub
            ->method('getRecordSemanticLinks')
            ->willReturn(new RecordSemanticLinkCollection());
        $stub
            ->method('getComments')
            ->willReturn(new CommentCollection());
        $stub
            ->method('getCommentsWithAnswers')
            ->willReturn(new CommentCollection());

        return $stub;
    }

    private function createArticleWithHtmlText(string $text): Article
    {
        $stub = $this->createMock(Article::class);
        $stub
            ->method('getText')
            ->willReturn($text);
        $stub
            ->method('getImages')
            ->willReturn(new ImageCollection());
        $stub
            ->method('isHtmlAllowed')
            ->willReturn(true);
        $stub
            ->method('getVotableId')
            ->willReturn(new VotableIdentifier('1', 'type'));
        $stub
            ->method('getRecordSemanticLinks')
            ->willReturn(new RecordSemanticLinkCollection());
        $stub
            ->method('getComments')
            ->willReturn(new CommentCollection());
        $stub
            ->method('getCommentsWithAnswers')
            ->willReturn(new CommentCollection());

        return $stub;
    }

    /**
     * @param SemanticLink[] $semanticLinks
     */
    private function createArticleWithSemanticLinks(string $text, array $semanticLinks): Article
    {
        $stub = $this->createMock(Article::class);
        $stub
            ->method('getText')
            ->willReturn($text);
        $stub
            ->method('getImages')
            ->willReturn(new ImageCollection());
        $stub
            ->method('isHtmlAllowed')
            ->willReturn(true);
        $stub
            ->method('getVotableId')
            ->willReturn(new VotableIdentifier('1', 'type'));
        $stub
            ->method('getRecordSemanticLinks')
            ->willReturn($this->createRecordSemanticLinksCollection($stub, $semanticLinks));
        $stub
            ->method('getComments')
            ->willReturn(new CommentCollection());
        $stub
            ->method('getCommentsWithAnswers')
            ->willReturn(new CommentCollection());

        return $stub;
    }

    /**
     * @param SemanticLink[] $semanticLinks
     */
    private function createRecordSemanticLinksCollection(Article $article, array $semanticLinks): RecordSemanticLinkCollection
    {
        $recordSemanticLinks = new RecordSemanticLinkCollection();

        foreach ($semanticLinks as $semanticLink) {
            $recordSemanticLinks->add(new RecordSemanticLink(Uuid::uuid4(), $article, $semanticLink, $semanticLink->getText()));
        }

        return $recordSemanticLinks;
    }
}
