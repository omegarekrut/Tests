<?php

namespace Tests\Functional\Domain\Record\Tidings\View;

use App\Domain\Comment\Collection\CommentCollection;
use App\Domain\Hashtag\Collection\HashtagCollection;
use App\Domain\Hashtag\Entity\Hashtag;
use App\Domain\Hashtag\Parser\HashtagParser;
use App\Domain\Record\Common\Collection\RecordSemanticLinkCollection;
use App\Domain\Record\Common\Entity\RecordSemanticLink;
use App\Domain\Record\Common\View\RecordViewCommonInformationFiller;
use App\Domain\Record\Common\View\RecordViewUrlGenerator;
use App\Domain\Record\Common\View\VideoUrlView;
use App\Domain\Record\Common\View\VideoUrlViewFactory;
use App\Domain\Record\Tidings\Entity\Tidings;
use App\Domain\Record\Tidings\ValueObject\FishingMethodCollection;
use App\Domain\Record\Tidings\View\TidingsFishingDetailsViewFactory;
use App\Domain\Record\Tidings\View\TidingsViewFactory;
use App\Domain\SemanticLink\Entity\SemanticLink;
use App\Module\Voting\Entity\VotableIdentifier;
use App\Module\YoutubeVideo\Collection\YoutubeVideoUrlCollection;
use App\Twig\Hashtag\HashtagLinkerFilter;
use App\Util\ImageStorage\Collection\ImageCollection;
use App\Util\ImageStorage\ImageTransformerFactory;
use App\Util\StringFilter\CleanedTextLineFilter;
use Carbon\Carbon;
use Ramsey\Uuid\Uuid;
use Tests\DataFixtures\ORM\LoadHashtags;
use Tests\DataFixtures\ORM\Record\Tidings\LoadNumberedTidings;
use Tests\DataFixtures\ORM\SemanticLink\LoadSemanticLinkWithValidUri;
use Tests\Functional\TestCase;

/**
 * @group record-view
 */
class TidingsViewFactoryTest extends TestCase
{
    private Tidings $tidings;
    private TidingsViewFactory $tidingsViewFactory;
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
            LoadNumberedTidings::class,
            LoadHashtags::class,
            LoadSemanticLinkWithValidUri::class,
        ])->getReferenceRepository();

        $this->tidings = $referenceRepository->getReference(LoadNumberedTidings::getRandReferenceName());
        $this->hashtag = $referenceRepository->getReference(LoadHashtags::getReferenceNameBySlug(LoadHashtags::HASHTAG_SLUG_WINTER_FISHING));
        $this->semanticLink = $referenceRepository->getReference(LoadSemanticLinkWithValidUri::REFERENCE_NAME);
        $this->recordViewUrlGenerator = $this->getContainer()->get(RecordViewUrlGenerator::class);
        $this->tidingsViewFactory = $this->createTidingsViewFactory();
        $this->cleanedTextLineFilter = $this->getContainer()->get(CleanedTextLineFilter::class);
        $this->imageTransformerFactory = $this->getContainer()->get(ImageTransformerFactory::class);
        $this->hashtagParser = $this->getContainer()->get(HashtagParser::class);
    }

    protected function tearDown(): void
    {
        unset(
            $this->tidings,
            $this->hashtag,
            $this->tidingsViewFactory,
            $this->recordViewUrlGenerator,
            $this->cleanedTextLineFilter,
            $this->imageTransformerFactory,
            $this->hashtagParser
        );

        parent::tearDown();
    }

    public function testMetadataTitleShouldBeEqualsTidingsTitle(): void
    {
        $tidingsView = $this->tidingsViewFactory->create($this->tidings);

        $this->assertEquals($this->tidings->getTitle(), $tidingsView->metadata->title);
    }

    public function testHeadingShouldBeEqualsTidingsTitle(): void
    {
        $tidingsView = $this->tidingsViewFactory->create($this->tidings);

        $this->assertEquals($this->tidings->getTitle(), $tidingsView->heading);
    }

    public function testMetadataViewUrlShouldLeadToPageForViewingTidings(): void
    {
        $tidingsView = $this->tidingsViewFactory->create($this->tidings);
        $viewTidingsPageUrl = $this->recordViewUrlGenerator->generate($this->tidings);

        $this->assertEquals($viewTidingsPageUrl, (string) $tidingsView->metadata->viewUrl);
    }

    public function testMetadataDescriptionAndPreviewShouldBeCreatedFromTidingsText(): void
    {
        $tidings = $this->createTidingsWithText('simple text');
        $tidingsView = $this->tidingsViewFactory->create($tidings);

        $this->assertEquals($tidings->getText(), $tidingsView->metadata->description);
        $this->assertEquals($tidings->getText(), $tidingsView->previewText);
    }

    public function testMetadataDescriptionAndPreviewShouldClearedByCleanedTextLineFilter(): void
    {
        $tidings = $this->createTidingsWithText("not prepared \n\n<b>preview</b> text");
        $tidingsView = $this->tidingsViewFactory->create($tidings);

        $cleanedTextLineFilter = $this->cleanedTextLineFilter;

        $this->assertEquals($cleanedTextLineFilter($tidings->getText()), $tidingsView->metadata->description);
        $this->assertEquals($cleanedTextLineFilter($tidings->getText()), $tidingsView->previewText);
    }

    public function testHtmlTextShouldBeCreatedFromTidingsText(): void
    {
        $tidings = $this->createTidingsWithText('simple text');
        $tidingsView = $this->tidingsViewFactory->create($tidings);

        $this->assertStringContainsString($tidings->getText(), $tidingsView->htmlText);
    }

    public function testHtmlTextShouldNotContainsHtmlSpecialCharsFromSource(): void
    {
        $tidings = $this->createTidingsWithText('<p>content</p>');
        $tidingsView = $this->tidingsViewFactory->create($tidings);

        $expectedText = htmlspecialchars($tidings->getText());

        $this->assertStringContainsString($expectedText, $tidingsView->htmlText);
    }

    public function testLineBreaksInTidingsHtmlShouldBePreparedLikeParagraphTags(): void
    {
        $tidings = $this->createTidingsWithText("First line\nSecond line");
        $tidingsView = $this->tidingsViewFactory->create($tidings);

        $expectedHtmlText = '<p>First line</p><p>Second line</p>';

        $this->assertStringContainsString($expectedHtmlText, $tidingsView->htmlText);
    }

    public function testSemanticLinksToViewTextInjector(): void
    {
        $tidings = $this->createTidingsWithSemanticLinks('Lorem ipsum dolor sit black hole hyper отзыв amet, consectetur adipiscing elit.', [$this->semanticLink]);

        $tidingsView = $this->tidingsViewFactory->create($tidings);

        $this->assertStringContainsString(
            'Lorem ipsum dolor sit <a href="/articles/view/86281/">black hole hyper отзыв</a> amet, consectetur adipiscing elit.',
            $tidingsView->htmlText
        );
    }

    public function testUrlsMustBeLinksInTidingsHtml(): void
    {
        $tidings = $this->createTidingsWithText('http://foo.bar');
        $tidingsView = $this->tidingsViewFactory->create($tidings);

        $expectedHtmlText = '<a href="http://foo.bar" target="_blank" rel="nofollow">http://foo.bar</a>';

        $this->assertStringContainsString($expectedHtmlText, $tidingsView->htmlText);
    }

    public function testTagsInHtmlTextShouldBeMarketAndAddedListTagList(): void
    {
        $tidings = $this->createTidingsWithText('#'.$this->hashtag->getName());
        $tidingsView = $this->tidingsViewFactory->create($tidings);

        $expectedHtmlText = $this->hashtagParser->addLinksToHashtags($tidings->getText(), new HashtagCollection([$this->hashtag]));

        $this->assertStringContainsString($expectedHtmlText, $tidingsView->htmlText);
    }

    public function testViewImageMustBeEqualsTidingsImageTransformers(): void
    {
        $tidingsView = $this->tidingsViewFactory->create($this->tidings);
        $expectedImages = $this->imageTransformerFactory->createByCollection($this->tidings->getImages());

        $this->assertEquals($expectedImages, $tidingsView->images);
    }

    public function testViewFishingDetailsMustBeFilledFromTidingsFishingDetails(): void
    {
        $tidings = $this->createTidingsWithFishingDetails();
        $tidingsView = $this->tidingsViewFactory->create($tidings);

        $this->assertStringContainsString($tidings->getDateStart()->format('d.m.Y'), $tidingsView->fishingDetails->fishingDates);
        $this->assertStringContainsString($tidings->getDateEnd()->format('d.m.Y'), $tidingsView->fishingDetails->fishingDates);
        $this->assertEquals((string) $tidings->getFishingMethods(), $tidingsView->fishingDetails->fishingMethods);
        $this->assertEquals($tidings->getFishingTime(), $tidingsView->fishingDetails->fishingTime);
        $this->assertEquals($tidings->getPlace(), $tidingsView->fishingDetails->place);
        $this->assertEquals($tidings->isHiddenPlace(), $tidingsView->fishingDetails->isHiddenPlace);
        $this->assertEquals($tidings->getTackles(), $tidingsView->fishingDetails->tackles);
        $this->assertEquals($tidings->getCatch(), $tidingsView->fishingDetails->catch);
        $this->assertEquals($tidings->getWeather(), $tidingsView->fishingDetails->weather);
    }

    public function testViewVideosShouldContainsAllTidingsVideoViews(): void
    {
        $tidingsWithVideos = $this->createTidingsWithVideos();
        $tidingsView = $this->tidingsViewFactory->create($tidingsWithVideos);

        $this->assertNotEmpty($tidingsView->videoUrls);

        foreach ($tidingsView->videoUrls as $video) {
            $this->assertInstanceOf(VideoUrlView::class, $video);
        }
    }

    private function createTidingsWithText(string $text): Tidings
    {
        $stub = $this->createMock(Tidings::class);
        $stub
            ->method('getText')
            ->willReturn($text);
        $stub
            ->method('getImages')
            ->willReturn(new ImageCollection());
        $stub
            ->method('getVideoUrls')
            ->willReturn(new YoutubeVideoUrlCollection());
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
    private function createTidingsWithSemanticLinks(string $text, array $semanticLinks): Tidings
    {
        $stub = $this->createMock(Tidings::class);
        $stub
            ->method('getText')
            ->willReturn($text);
        $stub
            ->method('getImages')
            ->willReturn(new ImageCollection());
        $stub
            ->method('getVideoUrls')
            ->willReturn(new YoutubeVideoUrlCollection());
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

    private function createTidingsWithFishingDetails(): Tidings
    {
        $stub = $this->createMock(Tidings::class);
        $stub
            ->method('getDateStart')
            ->willReturn(Carbon::now());
        $stub
            ->method('getDateEnd')
            ->willReturn(Carbon::now()->addYear());
        $stub
            ->method('getFishingMethods')
            ->willReturn(new FishingMethodCollection(['спиннинг', 'поплавочная удочка']));
        $stub
            ->method('getFishingTime')
            ->willReturn('some fishing time');
        $stub
            ->method('getPlace')
            ->willReturn('some place');
        $stub
            ->method('isHiddenPlace')
            ->willReturn(false);
        $stub
            ->method('getCatch')
            ->willReturn('some catch');
        $stub
            ->method('getWeather')
            ->willReturn('some weather');
        $stub
            ->method('getImages')
            ->willReturn(new ImageCollection());
        $stub
            ->method('getVotableId')
            ->willReturn(new VotableIdentifier('1', 'type'));
        $stub
            ->method('getVideoUrls')
            ->willReturn(new YoutubeVideoUrlCollection());
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

    private function createTidingsWithVideos(): Tidings
    {
        $stub = $this->createMock(Tidings::class);
        $stub
            ->method('getVideoUrls')
            ->willReturn(new YoutubeVideoUrlCollection(['//www.youtube.com/embed/qrBGpJNzWHk?rel=0&amp;enablejsapi=1']));
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

    /**
     * @param SemanticLink[] $semanticLinks
     */
    private function createRecordSemanticLinksCollection(Tidings $tidings, array $semanticLinks): RecordSemanticLinkCollection
    {
        $recordSemanticLinks = new RecordSemanticLinkCollection();

        foreach ($semanticLinks as $semanticLink) {
            $recordSemanticLinks->add(new RecordSemanticLink(Uuid::uuid4(), $tidings, $semanticLink, $semanticLink->getText()));
        }

        return $recordSemanticLinks;
    }

    private function createTidingsViewFactory(): TidingsViewFactory
    {
        return new TidingsViewFactory(
            $this->getContainer()->get(CleanedTextLineFilter::class),
            $this->recordViewUrlGenerator,
            $this->getContainer()->get(ImageTransformerFactory::class),
            $this->getContainer()->get(HashtagLinkerFilter::class),
            $this->getContainer()->get(VideoUrlViewFactory::class),
            $this->createMock(RecordViewCommonInformationFiller::class),
            $this->getContainer()->get(TidingsFishingDetailsViewFactory::class)
        );
    }
}
