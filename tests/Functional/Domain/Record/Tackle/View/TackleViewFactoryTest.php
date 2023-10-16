<?php

namespace Tests\Functional\Domain\Record\Tackle\View;

use App\Domain\Author\View\AuthorView;
use App\Domain\Comment\Collection\CommentCollection;
use App\Domain\Rating\ValueObject\RatingInfo;
use App\Domain\Record\Common\View\RecordViewCommonInformationFiller;
use App\Domain\Record\Common\View\RecordViewUrlGenerator;
use App\Domain\Record\Tackle\Collection\TackleReviewCollection;
use App\Domain\Record\Tackle\Entity\Tackle;
use App\Domain\Record\Tackle\Entity\TackleBrand;
use App\Domain\Record\Tackle\Entity\TackleReview;
use App\Domain\Record\Tackle\View\TackleReviewView;
use App\Domain\Record\Tackle\View\TackleReviewViewFactory;
use App\Domain\Record\Tackle\View\TackleViewFactory;
use App\Module\Voting\Entity\VotableIdentifier;
use App\Module\YoutubeVideo\Collection\YoutubeVideoUrlCollection;
use App\Util\ImageStorage\Collection\ImageCollection;
use App\Util\ImageStorage\ImageTransformerFactory;
use App\Util\StringFilter\CleanedTextLineFilter;
use Tests\DataFixtures\ORM\Record\LoadTackleReviews;
use Tests\Functional\TestCase;

/**
 * @group record-view
 */
class TackleViewFactoryTest extends TestCase
{
    private Tackle $tackle;
    private TackleViewFactory $tackleViewFactory;
    private RecordViewUrlGenerator $recordViewUrlGenerator;
    private CleanedTextLineFilter $cleanedTextLineFilter;
    private ImageTransformerFactory $imageTransformerFactory;

    protected function setUp(): void
    {
        parent::setUp();

        $referenceRepository = $this->loadFixtures([
            LoadTackleReviews::class,
        ])->getReferenceRepository();

        $tackleReview = $referenceRepository->getReference(LoadTackleReviews::getRandReferenceName());
        $this->tackle = $tackleReview->getTackle();
        $this->recordViewUrlGenerator = $this->getContainer()->get(RecordViewUrlGenerator::class);
        $this->tackleViewFactory = $this->createTackleViewFactory();
        $this->imageTransformerFactory = $this->getContainer()->get(ImageTransformerFactory::class);
        $this->cleanedTextLineFilter = $this->getContainer()->get(CleanedTextLineFilter::class);
    }

    protected function tearDown(): void
    {
        unset(
            $this->tackle,
            $this->tackleViewFactory,
            $this->recordViewUrlGenerator,
            $this->imageTransformerFactory,
            $this->cleanedTextLineFilter
        );

        parent::tearDown();
    }

    public function testCategoryShouldBeEqualsTackleCategory(): void
    {
        $tackleView = $this->tackleViewFactory->create($this->tackle);

        $this->assertEquals($this->tackle->getCategory(), $tackleView->category);
    }

    public function testLastReviewViewShouldBeEqualsTackleLastReviewViewS(): void
    {
        $tackleView = $this->tackleViewFactory->create($this->tackle);

        $this->assertInstanceOf(TackleReviewView::class, $tackleView->lastReviewView);
    }

    public function testMetadataTitleShouldBeEqualsTackleTitleWithReviewSuffix(): void
    {
        $tackleView = $this->tackleViewFactory->create($this->tackle);

        $this->assertEquals($this->tackle->getTitle().' - отзывы', $tackleView->metadata->title);
    }

    public function testHeadingShouldBeEqualsTackleTitle(): void
    {
        $tackleView = $this->tackleViewFactory->create($this->tackle);

        $this->assertEquals($this->tackle->getTitle(), $tackleView->heading);
    }

    public function testMetadataViewUrlShouldLeadToPageForViewingTackle(): void
    {
        $tackleView = $this->tackleViewFactory->create($this->tackle);
        $viewTacklePageUrl = $this->recordViewUrlGenerator->generate($this->tackle);

        $this->assertEquals($viewTacklePageUrl, (string) $tackleView->metadata->viewUrl);
    }

    public function testMetadataDescriptionShouldBeCreatedFromTackleDescriptionAndOtherDetails(): void
    {
        $tackle = $this->createTackleWithDetails(
            'some title',
            'brand title',
            'simple text',
            new RatingInfo(100, 100, 0, 100)
        );
        $tackleView = $this->tackleViewFactory->create($tackle);

        $this->assertStringContainsString($tackle->getTitle().' - отзывы', $tackleView->metadata->description);
        $this->assertStringContainsString('Бренд: '.$tackle->getBrand()->getTitle(), $tackleView->metadata->description);
        $this->assertStringContainsString('Средняя оценка 1 из 10', $tackleView->metadata->description);
        $this->assertStringContainsString('Всего отзывов: '.$tackle->getRatingInfo()->getVotesCount(), $tackleView->metadata->description);
        $this->assertStringContainsString('Достоинства и недостатки', $tackleView->metadata->description);
        $this->assertStringContainsString('Характеристики: '.$tackle->getDescription(), $tackleView->metadata->description);
    }

    public function testPreviewMustBeEmpty(): void
    {
        $tackleView = $this->tackleViewFactory->create($this->tackle);

        $this->assertEmpty($tackleView->previewText);
    }

    public function testMetadataDescriptionShouldClearedByCleanedTextLineFilter(): void
    {
        $tackle = $this->createTackleWithDescription("not prepared \n\n<b>preview</b> text");
        $tackleView = $this->tackleViewFactory->create($tackle);

        $cleanedTextLineFilter = $this->cleanedTextLineFilter;

        $this->assertStringContainsString($cleanedTextLineFilter($tackle->getDescription()), $tackleView->metadata->description);
    }

    public function testHtmlTextShouldBeCreatedFromTackleDescription(): void
    {
        $tackle = $this->createTackleWithDescription('simple text');
        $tackleView = $this->tackleViewFactory->create($tackle);

        $this->assertStringContainsString($tackle->getDescription(), $tackleView->htmlText);
    }

    public function testHtmlTextShouldNotContainsHtmlSpecialCharsFromSource(): void
    {
        $tackle = $this->createTackleWithDescription('<p>content</p>');
        $tackleView = $this->tackleViewFactory->create($tackle);

        $expectedText = htmlspecialchars($tackle->getDescription());

        $this->assertStringContainsString($expectedText, $tackleView->htmlText);
    }

    public function testLineBreaksInTackleHtmlShouldBePreparedLikeBrTags(): void
    {
        $tackle = $this->createTackleWithDescription("First line\nSecond line");
        $tackleView = $this->tackleViewFactory->create($tackle);

        $expectedHtmlText = "First line<br />\nSecond line";

        $this->assertStringContainsString($expectedHtmlText, $tackleView->htmlText);
    }

    public function testUrlsMustBeLinksInTackleHtml(): void
    {
        $tackle = $this->createTackleWithDescription('http://foo.bar');
        $tackleView = $this->tackleViewFactory->create($tackle);

        $expectedHtmlText = '<a href="http://foo.bar" target="_blank" rel="nofollow">http://foo.bar</a>';

        $this->assertStringContainsString($expectedHtmlText, $tackleView->htmlText);
    }

    public function testViewImageMustBeEqualsNewsImageTransformer(): void
    {
        $tackleView = $this->tackleViewFactory->create($this->tackle);
        $expectedImage = $this->imageTransformerFactory->create($this->tackle->getImage());

        $this->assertEquals($expectedImage, $tackleView->image);
    }

    public function testViewBrandMustBeEqualsTackleBrand(): void
    {
        $tackleView = $this->tackleViewFactory->create($this->tackle);

        $this->assertTrue($this->tackle->getBrand() === $tackleView->brand);
    }

    private function createTackleWithDescription(string $description): Tackle
    {
        $stub = $this->createMock(Tackle::class);
        $stub
            ->method('getDescription')
            ->willReturn($description);
        $stub
            ->method('getVotableId')
            ->willReturn(new VotableIdentifier('1', 'type'));

        $stub
            ->method('getReviews')
            ->willReturn(new TackleReviewCollection());
        $stub
            ->method('getComments')
            ->willReturn(new CommentCollection());
        $stub
            ->method('getCommentsWithAnswers')
            ->willReturn(new CommentCollection());

        return $stub;
    }

    private function createTackleWithDetails(string $title, string $brandTitle, string $text, RatingInfo $ratingInfo): Tackle
    {
        $brand = $this->createMock(TackleBrand::class);
        $brand
            ->method('getTitle')
            ->willReturn($brandTitle);

        $tackle = $this->createMock(Tackle::class);
        $tackle
            ->method('getTitle')
            ->willReturn($title);
        $tackle
            ->method('getDescription')
            ->willReturn($text);
        $tackle
            ->method('getBrand')
            ->willReturn($brand);
        $tackle
            ->method('getRatingInfo')
            ->willReturn($ratingInfo);

        $tackle
            ->method('getVotableId')
            ->willReturn(new VotableIdentifier('1', 'type'));

        $tackle
            ->method('getReviews')
            ->willReturn(new TackleReviewCollection([$this->createTackleReviewWithText('text')]));
        $tackle
            ->method('getComments')
            ->willReturn(new CommentCollection());
        $tackle
            ->method('getCommentsWithAnswers')
            ->willReturn(new CommentCollection());

        return $tackle;
    }

    private function createTackleReviewWithText(string $text): TackleReview
    {
        $stub = $this->createMock(TackleReview::class);

        $stub
            ->method('getText')
            ->willReturn($text);
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
            ->method('getComments')
            ->willReturn(new CommentCollection());
        $stub
            ->method('getCommentsWithAnswers')
            ->willReturn(new CommentCollection());

        return $stub;
    }

    private function createTackleViewFactory(): TackleViewFactory
    {
        return new TackleViewFactory(
            $this->getContainer()->get(CleanedTextLineFilter::class),
            $this->recordViewUrlGenerator,
            $this->getContainer()->get(ImageTransformerFactory::class),
            $this->createMock(RecordViewCommonInformationFiller::class),
            $this->getContainer()->get(TackleReviewViewFactory::class)
        );
    }
}
