<?php

namespace Tests\Functional\Domain\Record\Tackle\View;

use App\Domain\Comment\Collection\CommentCollection;
use App\Domain\Record\Common\View\RecordViewCommonInformationFiller;
use App\Domain\Record\Common\View\RecordViewUrlGenerator;
use App\Domain\Record\Common\View\VideoUrlView;
use App\Domain\Record\Common\View\VideoUrlViewFactory;
use App\Domain\Record\Tackle\Entity\Tackle;
use App\Domain\Record\Tackle\Entity\TackleReview;
use App\Domain\Record\Tackle\View\TackleReviewViewFactory;
use App\Module\Author\AnonymousAuthor;
use App\Module\Author\AuthorInterface;
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
class TackleReviewViewFactoryTest extends TestCase
{
    private TackleReview $tackleReview;
    private TackleReviewViewFactory $tackleReviewViewFactory;
    private RecordViewUrlGenerator $recordViewUrlGenerator;
    private CleanedTextLineFilter $cleanedTextLineFilter;
    private ImageTransformerFactory $imageTransformerFactory;

    protected function setUp(): void
    {
        parent::setUp();

        $referenceRepository = $this->loadFixtures([
            LoadTackleReviews::class,
        ])->getReferenceRepository();

        $this->tackleReview = $referenceRepository->getReference(LoadTackleReviews::getRandReferenceName());
        $this->recordViewUrlGenerator = $this->getContainer()->get(RecordViewUrlGenerator::class);
        $this->tackleReviewViewFactory = $this->createTackleReviewViewFactory();
        $this->cleanedTextLineFilter = $this->getContainer()->get(CleanedTextLineFilter::class);
        $this->imageTransformerFactory = $this->getContainer()->get(ImageTransformerFactory::class);
    }

    protected function tearDown(): void
    {
        unset(
            $this->tackleReview,
            $this->tackleReviewViewFactory,
            $this->recordViewUrlGenerator,
            $this->cleanedTextLineFilter,
            $this->imageTransformerFactory
        );

        parent::tearDown();
    }

    public function testExperienceShouldBeEqualsTackleReviewExperience(): void
    {
        $tackleReviewView = $this->tackleReviewViewFactory->create($this->tackleReview);

        $this->assertEquals($this->tackleReview->getTackle()->getCategory(), $tackleReviewView->category);
        $this->assertEquals($this->tackleReview->getExperience(), $tackleReviewView->experience);
    }

    public function testMetadataTitleShouldBeEqualsTackleReviewTitleWithReviewSuffix(): void
    {
        $tackleReviewView = $this->tackleReviewViewFactory->create($this->tackleReview);

        $this->assertEquals($this->tackleReview->getTitle(), $tackleReviewView->metadata->title);
    }

    public function testHeadingShouldBeEqualsTackleReviewTitle(): void
    {
        $tackleReviewView = $this->tackleReviewViewFactory->create($this->tackleReview);

        $this->assertEquals($this->tackleReview->getTitle(), $tackleReviewView->heading);
    }

    public function testMetadataViewUrlShouldLeadToPageForViewingTackleReview(): void
    {
        $tackleReviewView = $this->tackleReviewViewFactory->create($this->tackleReview);
        $viewTackleReviewPageUrl = $this->recordViewUrlGenerator->generate($this->tackleReview);

        $this->assertEquals($viewTackleReviewPageUrl, (string) $tackleReviewView->metadata->viewUrl);
    }

    public function testMetadataDescriptionShouldBeCreatedFromTackleReviewTextAndOtherDetails(): void
    {
        $tackleReview = $this->createTackleReviewWithDetails(
            'tackle title',
            'simple text',
            new AnonymousAuthor('review author'),
            10,
            100,
            'good values',
            'bed values'
        );
        $tackleReviewView = $this->tackleReviewViewFactory->create($tackleReview);

        $this->assertStringContainsString($tackleReview->getTackle()->getTitle(), $tackleReviewView->metadata->description);
        $this->assertStringContainsString('отзыв пользователя '.$tackleReview->getAuthor()->getUsername(), $tackleReviewView->metadata->description);
        $this->assertStringContainsString('Оценка: '.$tackleReview->getGrade(), $tackleReviewView->metadata->description);
        $this->assertStringContainsString('Комментариев: '.$tackleReview->getCommentsCount(), $tackleReviewView->metadata->description);
        $this->assertStringContainsString('Достоинства: '.$tackleReview->getGoodValues(), $tackleReviewView->metadata->description);
        $this->assertStringContainsString('Недостатки: '.$tackleReview->getBadValues(), $tackleReviewView->metadata->description);
        $this->assertStringContainsString($tackleReview->getText(), $tackleReviewView->metadata->description);
        $this->assertEquals($tackleReview->getTackle()->getTitle(), $tackleReviewView->tackleHeading);
        $this->assertEquals(sprintf('/tackles/view/%d/', $tackleReview->getTackle()->getId()), (string) $tackleReviewView->tackleViewUrl);
    }

    public function testPreviewShouldBeCreatedFromReviewText(): void
    {
        $tackleReview = $this->createTackleReviewWithText('plain text');
        $tackleReviewView = $this->tackleReviewViewFactory->create($tackleReview);

        $this->assertEquals($tackleReview->getText(), $tackleReviewView->previewText);
    }

    public function testDescriptionAndPreviewShouldClearedByCleanedTextLineFilter(): void
    {
        $tackleReview = $this->createTackleReviewWithText("not prepared \n\n<b>preview</b> text");
        $tackleReviewView = $this->tackleReviewViewFactory->create($tackleReview);

        $cleanedTextLineFilter = $this->cleanedTextLineFilter;

        $this->assertStringContainsString($cleanedTextLineFilter($tackleReview->getText()), $tackleReviewView->metadata->description);
        $this->assertStringContainsString($cleanedTextLineFilter($tackleReview->getText()), $tackleReviewView->previewText);
    }

    public function testHtmlTextShouldBeCreatedFromTackleReviewText(): void
    {
        $tackleReview = $this->createTackleReviewWithText('simple text');
        $tackleReviewView = $this->tackleReviewViewFactory->create($tackleReview);

        $this->assertStringContainsString($tackleReview->getText(), $tackleReviewView->htmlText);
    }

    public function testHtmlTextShouldNotContainsHtmlSpecialCharsFromSource(): void
    {
        $tackleReview = $this->createTackleReviewWithText('<p>content</p>');
        $tackleReviewView = $this->tackleReviewViewFactory->create($tackleReview);

        $expectedText = htmlspecialchars($tackleReview->getText());

        $this->assertStringContainsString($expectedText, $tackleReviewView->htmlText);
    }

    public function testLineBreaksInTackleReviewHtmlShouldBePreparedLikeBrTags(): void
    {
        $tackleReview = $this->createTackleReviewWithText("First line\nSecond line");
        $tackleReviewView = $this->tackleReviewViewFactory->create($tackleReview);

        $expectedHtmlText = "First line<br />\nSecond line";

        $this->assertStringContainsString($expectedHtmlText, $tackleReviewView->htmlText);
    }

    public function testUrlsMustBeLinksInTackleReviewHtml(): void
    {
        $tackleReview = $this->createTackleReviewWithText('http://foo.bar');
        $tackleReviewView = $this->tackleReviewViewFactory->create($tackleReview);

        $expectedHtmlText = '<a href="http://foo.bar" target="_blank" rel="nofollow">http://foo.bar</a>';

        $this->assertStringContainsString($expectedHtmlText, $tackleReviewView->htmlText);
    }

    public function testViewImageMustBeEqualsTackleReviewImageTransformers(): void
    {
        $tackleReviewView = $this->tackleReviewViewFactory->create($this->tackleReview);
        $expectedImages = $this->imageTransformerFactory->createByCollection($this->tackleReview->getImages());

        $this->assertEquals($expectedImages, $tackleReviewView->images);
    }

    public function testViewReviewDetailsMustBeEqualsTackleReviewDetails(): void
    {
        $tackleReviewView = $this->tackleReviewViewFactory->create($this->tackleReview);

        $this->assertEquals($this->tackleReview->getGrade(), $tackleReviewView->grade);
        $this->assertEquals($this->tackleReview->getGoodValues(), $tackleReviewView->goodValues);
        $this->assertEquals($this->tackleReview->getBadValues(), $tackleReviewView->badValues);
        $this->assertEquals($this->tackleReview->getExperience(), $tackleReviewView->experience);

        $this->assertEquals($this->tackleReview->getTackle()->getTitle(), $tackleReviewView->tackleHeading);
        $this->assertEquals(sprintf('/tackles/view/%d/', $this->tackleReview->getTackle()->getId()), (string) $tackleReviewView->tackleViewUrl);
    }

    public function testViewVideosShouldContainsAllReviewVideoViews(): void
    {
        $tackleWithVideos = $this->createTackleReviewWithVideos();
        $tackleReviewView = $this->tackleReviewViewFactory->create($tackleWithVideos);

        $this->assertNotEmpty($tackleReviewView->videoUrls);

        foreach ($tackleReviewView->videoUrls as $video) {
            $this->assertInstanceOf(VideoUrlView::class, $video);
        }
    }

    private function createTackleReviewWithText(string $text): TackleReview
    {
        $tackle = $this->createMock(Tackle::class);
        $tackle
            ->method('getTitle')
            ->willReturn('tackle title');
        $tackle
            ->method('getVotableId')
            ->willReturn(new VotableIdentifier('42', 'type'));

        $stub = $this->createMock(TackleReview::class);
        $stub
            ->method('getTackle')
            ->willReturn($tackle);
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

    private function createTackleReviewWithDetails(
        string $tackleTitle,
        string $reviewText,
        AuthorInterface $reviewAuthor,
        int $reviewGrade,
        int $reviewCommentCount,
        string $reviewGoodValues,
        string $reviewBadValues
    ): TackleReview {

        $tackle = $this->createMock(Tackle::class);
        $tackle
            ->method('getTitle')
            ->willReturn($tackleTitle);
        $tackle
            ->method('getVotableId')
            ->willReturn(new VotableIdentifier('42', 'type'));

        $review = $this->createMock(TackleReview::class);
        $review
            ->method('getTackle')
            ->willReturn($tackle);
        $review
            ->method('getText')
            ->willReturn($reviewText);
        $review
            ->method('getAuthor')
            ->willReturn($reviewAuthor);
        $review
            ->method('getGrade')
            ->willReturn($reviewGrade);
        $review
            ->method('getCommentsCount')
            ->willReturn($reviewCommentCount);
        $review
            ->method('getGoodValues')
            ->willReturn($reviewGoodValues);
        $review
            ->method('getBadValues')
            ->willReturn($reviewBadValues);
        $review
            ->method('getImages')
            ->willReturn(new ImageCollection());
        $review
            ->method('getVotableId')
            ->willReturn(new VotableIdentifier('1', 'type'));
        $review
            ->method('getVideoUrls')
            ->willReturn(new YoutubeVideoUrlCollection());
        $review
            ->method('getComments')
            ->willReturn(new CommentCollection());
        $review
            ->method('getCommentsWithAnswers')
            ->willReturn(new CommentCollection());

        return $review;
    }

    private function createTackleReviewWithVideos(): TackleReview
    {
        $tackle = $this->createMock(Tackle::class);
        $tackle
            ->method('getTitle')
            ->willReturn('tackle title');
        $tackle
            ->method('getVotableId')
            ->willReturn(new VotableIdentifier('42', 'type'));

        $stub = $this->createMock(TackleReview::class);
        $stub
            ->method('getTackle')
            ->willReturn($tackle);
        $stub
            ->method('getVideoUrls')
            ->willReturn(new YoutubeVideoUrlCollection(['//www.youtube.com/embed/qrBGpJNzWHk?rel=0&amp;enablejsapi=1']));
        $stub
            ->method('areCommentsDisallowed')
            ->willReturn(false);
        $stub
            ->method('getImages')
            ->willReturn(new ImageCollection());
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

    private function createTackleReviewViewFactory(): TackleReviewViewFactory
    {
        return new TackleReviewViewFactory(
            $this->getContainer()->get(CleanedTextLineFilter::class),
            $this->recordViewUrlGenerator,
            $this->getContainer()->get(ImageTransformerFactory::class),
            $this->getContainer()->get(VideoUrlViewFactory::class),
            $this->getContainer()->get(RecordViewCommonInformationFiller::class)
        );
    }
}
