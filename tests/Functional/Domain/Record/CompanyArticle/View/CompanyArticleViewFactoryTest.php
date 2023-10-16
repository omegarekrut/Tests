<?php

namespace Tests\Functional\Domain\Record\CompanyArticle\View;

use App\Domain\Comment\Collection\CommentCollection;
use App\Domain\Company\Entity\Company;
use App\Domain\Company\View\CompanyView;
use App\Domain\Company\View\CompanyViewFactory;
use App\Domain\Hashtag\Collection\HashtagCollection;
use App\Domain\Hashtag\Entity\Hashtag;
use App\Domain\Hashtag\Parser\HashtagParser;
use App\Domain\Record\Common\Collection\RecordSemanticLinkCollection;
use App\Domain\Record\Common\Entity\RecordSemanticLink;
use App\Domain\Record\Common\View\RecordViewCommonInformationFiller;
use App\Domain\Record\Common\View\RecordViewMetadata;
use App\Domain\Record\Common\View\RecordViewUrlGenerator;
use App\Domain\Record\Common\View\VideoUrlView;
use App\Domain\Record\Common\View\VideoUrlViewFactory;
use App\Domain\Record\CompanyArticle\Entity\CompanyArticle;
use App\Domain\Record\CompanyArticle\View\CompanyArticleViewFactory;
use App\Domain\SemanticLink\Entity\SemanticLink;
use App\Module\Voting\Entity\VotableIdentifier;
use App\Module\YoutubeVideo\Collection\YoutubeVideoUrlCollection;
use App\Twig\Hashtag\HashtagLinkerFilter;
use App\Util\ImageStorage\Collection\ImageCollection;
use App\Util\ImageStorage\ImageTransformerFactory;
use App\Util\StringFilter\BBCode\BBCodeToHtmlFilter;
use App\Util\StringFilter\CleanedTextLineFilter;
use Ramsey\Uuid\Uuid;
use Tests\DataFixtures\ORM\Company\Company\LoadAquaMotorcycleShopsCompany;
use Tests\DataFixtures\ORM\LoadHashtags;
use Tests\DataFixtures\ORM\Record\CompanyArticle\LoadAquaMotorcycleShopsCompanyArticle;
use Tests\DataFixtures\ORM\SemanticLink\LoadSemanticLinkWithValidUri;
use Tests\Functional\TestCase;

/**
 * @group record-view
 */
class CompanyArticleViewFactoryTest extends TestCase
{
    private CompanyArticle $companyArticle;
    private Company $company;
    private CompanyArticleViewFactory $companyArticleViewFactory;
    private CompanyView $companyView;
    private CompanyViewFactory $companyViewFactory;
    private ImageTransformerFactory $imageTransformerFactory;
    private Hashtag $hashtag;
    private HashtagParser $hashtagParser;
    private SemanticLink $semanticLink;

    protected function setUp(): void
    {
        parent::setUp();

        $referenceRepository = $this->loadFixtures([
            LoadAquaMotorcycleShopsCompanyArticle::class,
            LoadAquaMotorcycleShopsCompany::class,
            LoadHashtags::class,
            LoadSemanticLinkWithValidUri::class,
        ])->getReferenceRepository();

        $this->companyArticle = $referenceRepository->getReference(LoadAquaMotorcycleShopsCompanyArticle::REFERENCE_NAME);
        $this->company = $referenceRepository->getReference(LoadAquaMotorcycleShopsCompany::REFERENCE_NAME);
        $this->companyView = $this->createCompanyView();
        $this->companyViewFactory = $this->createCompanyViewFactory();
        $this->companyArticleViewFactory = $this->createCompanyArticleViewFactory();
        $this->imageTransformerFactory = $this->getContainer()->get(ImageTransformerFactory::class);
        $this->hashtag = $referenceRepository->getReference(LoadHashtags::getReferenceNameBySlug(LoadHashtags::HASHTAG_SLUG_WINTER_FISHING));
        $this->hashtagParser = $this->getContainer()->get(HashtagParser::class);
        $this->semanticLink = $referenceRepository->getReference(LoadSemanticLinkWithValidUri::REFERENCE_NAME);
    }

    protected function tearDown(): void
    {
        unset(
            $this->companyArticle,
            $this->company,
            $this->companyArticleViewFactory,
            $this->companyViewFactory,
            $this->imageTransformerFactory,
            $this->hashtag,
            $this->hashtagParser,
            $this->semanticLink
        );

        parent::tearDown();
    }

    public function testHeadingShouldBeEqualsCompanyArticleTitle(): void
    {
        $companyArticleView = $this->companyArticleViewFactory->create($this->companyArticle);

        $this->assertEquals($this->companyArticle->getTitle(), $companyArticleView->heading);
    }

    public function testHtmlTextShouldBeCreatedFromCompanyArticleText(): void
    {
        $companyArticle = $this->createCompanyArticleWithText('simple text');
        $companyArticleView = $this->companyArticleViewFactory->create($companyArticle);

        $this->assertStringContainsString($companyArticle->getText(), $companyArticleView->htmlText);
    }

    public function testHtmlTextShouldNotContainsHtmlSpecialCharsFromSource(): void
    {
        $companyArticle = $this->createCompanyArticleWithText('<p>content</p>');
        $companyArticleView = $this->companyArticleViewFactory->create($companyArticle);

        $expectedText = htmlspecialchars($companyArticle->getText());

        $this->assertStringContainsString($expectedText, $companyArticleView->htmlText);
    }

    public function testBBCodesShouldBeFormattedToHtml(): void
    {
        $companyArticle = $this->createCompanyArticleWithText('[b]content[/b]');
        $companyArticleView = $this->companyArticleViewFactory->create($companyArticle);

        $expectedText = '<b>content</b>';

        $this->assertStringContainsString($expectedText, $companyArticleView->htmlText);
    }

    public function testLineBreaksInCompanyArticleHtmlShouldBePreparedLikeParagraphTags(): void
    {
        $companyArticle = $this->createCompanyArticleWithText("First line\nSecond line");
        $companyArticleView = $this->companyArticleViewFactory->create($companyArticle);

        $expectedHtmlText = '<p>First line</p><p>Second line</p>';

        $this->assertStringContainsString($expectedHtmlText, $companyArticleView->htmlText);
    }

    public function testSemanticLinksToViewTextInjector(): void
    {
        $companyArticle = $this->createCompanyArticleWithSemanticLinks('Lorem ipsum dolor sit black hole hyper отзыв amet, consectetur adipiscing elit.', [$this->semanticLink]);

        $companyArticleView = $this->companyArticleViewFactory->create($companyArticle);

        $this->assertStringContainsString(
            'Lorem ipsum dolor sit <a href="/articles/view/86281/">black hole hyper отзыв</a> amet, consectetur adipiscing elit.',
            $companyArticleView->htmlText
        );
    }

    public function testUrlsMustBeLinksInCompanyArticleHtml(): void
    {
        $companyArticle = $this->createCompanyArticleWithText('http://foo.bar');
        $companyArticleView = $this->companyArticleViewFactory->create($companyArticle);

        $expectedHtmlText = '<a href="http://foo.bar" target="_blank" rel="nofollow">http://foo.bar</a>';

        $this->assertStringContainsString($expectedHtmlText, $companyArticleView->htmlText);
    }

    public function testTagsInHtmlTextShouldBeMarketAndAddedListTagList(): void
    {
        $companyArticle = $this->createCompanyArticleWithText('#'.$this->hashtag->getName());
        $companyArticleView = $this->companyArticleViewFactory->create($companyArticle);

        $expectedHtmlText = $this->hashtagParser->addLinksToHashtags($companyArticle->getText(), new HashtagCollection([$this->hashtag]));

        $this->assertStringContainsString($expectedHtmlText, $companyArticleView->htmlText);
    }

    public function testViewImageMustBeEqualsCompanyArticleImageTransformers(): void
    {
        $companyArticleView = $this->companyArticleViewFactory->create($this->companyArticle);
        $expectedImages = $this->imageTransformerFactory->createByCollection($this->companyArticle->getImages());

        $this->assertEquals($expectedImages, $companyArticleView->images);
    }

    public function testViewVideosShouldContainsAllCompanyArticleVideoViews(): void
    {
        $companyArticleWithVideos = $this->createCompanyArticleWithVideos();
        $companyArticleView = $this->companyArticleViewFactory->create($companyArticleWithVideos);

        $this->assertNotEmpty($companyArticleView->videoUrls);

        foreach ($companyArticleView->videoUrls as $video) {
            $this->assertInstanceOf(VideoUrlView::class, $video);
        }
    }

    public function testViewCompanyMustBeEqualsCompanyArticleCompany(): void
    {
        $companyArticleView = $this->companyArticleViewFactory->create($this->companyArticle);
        $expectedCompanyView = $this->companyViewFactory->create($this->company);

        $this->assertEquals($expectedCompanyView, $companyArticleView->company);
    }

    public function testViewPreviewMustBeEqualsCompanyArticlePreview(): void
    {
        $companyArticleView = $this->companyArticleViewFactory->create($this->companyArticle);

        $this->assertEquals($companyArticleView->preview, $companyArticleView->preview);
    }

    public function testMetadataWasGenerated(): void
    {
        $article = $this->createCompanyArticleWithText('text');

        $view = $this->companyArticleViewFactory->create($article);
        $metadata = $view->metadata;

        $this->assertInstanceOf(RecordViewMetadata::class, $metadata);

        $this->assertEquals($article->getTitle(), $metadata->title);
        $this->assertEquals($article->getText(), $metadata->description);
        $this->assertEquals(sprintf('/company-articles/view/%d/', $article->getId()), $metadata->viewUrl);
    }

    private function createCompanyArticleWithText(string $text): CompanyArticle
    {
        $companyArticleMock = $this->createMock(CompanyArticle::class);

        $companyArticleMock
            ->method('getText')
            ->willReturn($text);
        $companyArticleMock
            ->method('getImages')
            ->willReturn(new ImageCollection());
        $companyArticleMock
            ->method('getVideoUrls')
            ->willReturn(new YoutubeVideoUrlCollection());
        $companyArticleMock
            ->method('getVotableId')
            ->willReturn(new VotableIdentifier('1', 'type'));
        $companyArticleMock
            ->method('getRecordSemanticLinks')
            ->willReturn(new RecordSemanticLinkCollection());
        $companyArticleMock
            ->method('getCompanyAuthor')
            ->willReturn($this->company);
        $companyArticleMock
            ->method('getComments')
            ->willReturn(new CommentCollection());
        $companyArticleMock
            ->method('getCommentsWithAnswers')
            ->willReturn(new CommentCollection());

        return $companyArticleMock;
    }

    /**
     * @param SemanticLink[] $semanticLinks
     */
    private function createCompanyArticleWithSemanticLinks(string $text, array $semanticLinks): CompanyArticle
    {
        $companyArticleMock = $this->createMock(CompanyArticle::class);

        $companyArticleMock
            ->method('getText')
            ->willReturn($text);
        $companyArticleMock
            ->method('getImages')
            ->willReturn(new ImageCollection());
        $companyArticleMock
            ->method('getVideoUrls')
            ->willReturn(new YoutubeVideoUrlCollection());
        $companyArticleMock
            ->method('getVotableId')
            ->willReturn(new VotableIdentifier('1', 'type'));
        $companyArticleMock
            ->method('getRecordSemanticLinks')
            ->willReturn($this->createRecordSemanticLinksCollection($companyArticleMock, $semanticLinks));
        $companyArticleMock
            ->method('getCompanyAuthor')
            ->willReturn($this->company);
        $companyArticleMock
            ->method('getComments')
            ->willReturn(new CommentCollection());
        $companyArticleMock
            ->method('getCommentsWithAnswers')
            ->willReturn(new CommentCollection());

        return $companyArticleMock;
    }

    private function createCompanyArticleWithVideos(): CompanyArticle
    {
        $companyArticleMock = $this->createMock(CompanyArticle::class);

        $companyArticleMock
            ->method('getVideoUrls')
            ->willReturn(new YoutubeVideoUrlCollection(['//www.youtube.com/embed/qrBGpJNzWHk?rel=0&amp;enablejsapi=1']));
        $companyArticleMock
            ->method('getImages')
            ->willReturn(new ImageCollection());
        $companyArticleMock
            ->method('getVotableId')
            ->willReturn(new VotableIdentifier('1', 'type'));
        $companyArticleMock
            ->method('getRecordSemanticLinks')
            ->willReturn(new RecordSemanticLinkCollection());
        $companyArticleMock
            ->method('getCompanyAuthor')
            ->willReturn($this->company);
        $companyArticleMock
            ->method('getComments')
            ->willReturn(new CommentCollection());
        $companyArticleMock
            ->method('getCommentsWithAnswers')
            ->willReturn(new CommentCollection());

        return $companyArticleMock;
    }

    private function createCompanyArticleViewFactory(): CompanyArticleViewFactory
    {
        return new CompanyArticleViewFactory(
            $this->getContainer()->get(CleanedTextLineFilter::class),
            $this->getContainer()->get(ImageTransformerFactory::class),
            $this->getContainer()->get(HashtagLinkerFilter::class),
            $this->getContainer()->get(VideoUrlViewFactory::class),
            $this->createMock(RecordViewCommonInformationFiller::class),
            $this->companyViewFactory,
            $this->getContainer()->get(BBCodeToHtmlFilter::class),
            $this->getContainer()->get(RecordViewUrlGenerator::class)
        );
    }

    private function createCompanyViewFactory(): CompanyViewFactory
    {
        $companyViewFactory = $this->createMock(CompanyViewFactory::class);
        $companyViewFactory->method('create')->willReturn($this->companyView);

        return $companyViewFactory;
    }

    private function createCompanyView(): CompanyView
    {
        return $this->createMock(CompanyView::class);
    }

    /**
     * @param SemanticLink[] $semanticLinks
     */
    private function createRecordSemanticLinksCollection(CompanyArticle $companyArticle, array $semanticLinks): RecordSemanticLinkCollection
    {
        $recordSemanticLinks = new RecordSemanticLinkCollection();

        foreach ($semanticLinks as $semanticLink) {
            $recordSemanticLinks->add(new RecordSemanticLink(Uuid::uuid4(), $companyArticle, $semanticLink, $semanticLink->getText()));
        }

        return $recordSemanticLinks;
    }
}
