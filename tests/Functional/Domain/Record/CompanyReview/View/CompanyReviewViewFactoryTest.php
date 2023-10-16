<?php

namespace Tests\Functional\Domain\Record\CompanyReview\View;

use App\Domain\Comment\Collection\CommentCollection;
use App\Domain\Company\Entity\Company;
use App\Domain\Company\View\CompanyViewFactory;
use App\Domain\Record\Common\View\RecordViewMetadata;
use App\Domain\Record\CompanyReview\Entity\CompanyReview;
use App\Domain\Record\CompanyReview\View\CompanyReviewViewFactory;
use App\Module\Voting\Entity\VotableIdentifier;
use App\Util\ImageStorage\Collection\ImageCollection;
use App\Util\ImageStorage\ImageTransformerFactory;
use Tests\DataFixtures\ORM\Company\Company\LoadAquaMotorcycleShopsCompany;
use Tests\DataFixtures\ORM\Record\CompanyReview\LoadCompanyReviews;
use Tests\Functional\TestCase;

/**
 * @group record-view
 */
class CompanyReviewViewFactoryTest extends TestCase
{
    private CompanyReview $companyReview;
    private Company $company;
    private CompanyReviewViewFactory $companyReviewViewFactory;
    private CompanyViewFactory $companyViewFactory;
    private ImageTransformerFactory $imageTransformerFactory;

    protected function setUp(): void
    {
        parent::setUp();

        $referenceRepository = $this->loadFixtures([
            LoadCompanyReviews::class,
        ])->getReferenceRepository();

        $this->companyReview = $referenceRepository->getReference(LoadCompanyReviews::REFERENCE_NAME);
        $this->company = $referenceRepository->getReference(LoadAquaMotorcycleShopsCompany::REFERENCE_NAME);
        $this->companyReviewViewFactory = $this->getContainer()->get(CompanyReviewViewFactory::class);
        $this->companyViewFactory = $this->getContainer()->get(CompanyViewFactory::class);
        $this->imageTransformerFactory = $this->getContainer()->get(ImageTransformerFactory::class);
    }

    protected function tearDown(): void
    {
        unset(
            $this->companyReview,
            $this->company,
            $this->companyReviewViewFactory,
            $this->companyViewFactory,
            $this->imageTransformerFactory,
        );

        parent::tearDown();
    }

    public function testCommonViewInformationShouldBeEqualsCommonRecordInfo(): void
    {
        $companyReviewView = $this->companyReviewViewFactory->create($this->companyReview);

        $this->assertEquals($this->companyReview->getId(), $companyReviewView->id);

        $this->assertEquals($this->companyReview->getAuthor()->getId(), $companyReviewView->author->id);
        $this->assertEquals($this->companyReview->getAuthor()->getUsername(), $companyReviewView->author->name);
        $this->assertEquals($this->companyReview->getAuthor()->getSubscribers(), $companyReviewView->author->subscribers);

        $this->assertEquals($this->companyReview->getRatingInfo(), $companyReviewView->ratingInfo);
        $this->assertEquals($this->companyReview->getVotableId(), $companyReviewView->votableId);
        $this->assertEquals($this->companyReview->getPriority(), $companyReviewView->priority);
        $this->assertEquals($this->companyReview->getViews(), $companyReviewView->views);
        $this->assertEquals($this->companyReview->areCommentsDisplayed(), $companyReviewView->areCommentsDisplayed);
        $this->assertEquals($this->companyReview->areCommentsOnlyRead(), $companyReviewView->areCommentsOnlyRead);
        $this->assertEquals($this->companyReview->areCommentsDisallowed(), $companyReviewView->areCommentsDisallowed);
        $this->assertEquals($this->companyReview->getCommentsCount(), $companyReviewView->commentsCount);
        $this->assertEquals($this->companyReview->getCreatedAt(), $companyReviewView->createdAt);
    }

    public function testHeadingShouldBeEqualsCompanyReviewTitle(): void
    {
        $companyReviewView = $this->companyReviewViewFactory->create($this->companyReview);

        $this->assertEquals($this->companyReview->getTitle(), $companyReviewView->heading);
    }

    public function testHtmlTextShouldBeCreatedFromCompanyReviewText(): void
    {
        $companyReview = $this->createCompanyReviewWithText('some text');
        $companyReviewView = $this->companyReviewViewFactory->create($companyReview);

        $this->assertStringContainsString($companyReview->getText(), $companyReviewView->htmlText);
    }

    public function testHtmlTextShouldNotContainsHtmlSpecialCharsFromSource(): void
    {
        $companyReview = $this->createCompanyReviewWithText('<p>content</p>');
        $companyReviewView = $this->companyReviewViewFactory->create($companyReview);

        $expectedText = htmlspecialchars($companyReview->getText());

        $this->assertStringContainsString($expectedText, $companyReviewView->htmlText);
    }

    public function testLineBreaksInCompanyReviewHtmlShouldBePreparedLikeBrTags(): void
    {
        $companyReview = $this->createCompanyReviewWithText("First line\nSecond line");
        $companyReviewView = $this->companyReviewViewFactory->create($companyReview);

        $expectedHtmlText = "First line<br />\nSecond line";

        $this->assertStringContainsString($expectedHtmlText, $companyReviewView->htmlText);
    }

    public function testUrlsMustBeLinksInCompanyReviewHtml(): void
    {
        $companyReview = $this->createCompanyReviewWithText('http://foo.bar');
        $companyReviewView = $this->companyReviewViewFactory->create($companyReview);

        $expectedHtmlText = '<a href="http://foo.bar" target="_blank" rel="nofollow">http://foo.bar</a>';

        $this->assertStringContainsString($expectedHtmlText, $companyReviewView->htmlText);
    }

    public function testViewImageMustBeEqualsCompanyReviewImageTransformers(): void
    {
        $companyReviewView = $this->companyReviewViewFactory->create($this->companyReview);
        $expectedImages = $this->imageTransformerFactory->createByCollection($this->companyReview->getImages());

        $this->assertEquals($expectedImages, $companyReviewView->images);
    }

    public function testViewCompanyMustBeEqualsCompanyReviewCompany(): void
    {
        $companyReviewView = $this->companyReviewViewFactory->create($this->companyReview);
        $expectedCompanyView = $this->companyViewFactory->create($this->company);

        $this->assertEquals($expectedCompanyView, $companyReviewView->company);
    }

    public function testMetadataWasGenerated(): void
    {
        $review = $this->createCompanyReviewWithText('text');

        $view = $this->companyReviewViewFactory->create($review);
        $metadata = $view->metadata;

        $this->assertInstanceOf(RecordViewMetadata::class, $metadata);

        $this->assertEquals($review->getTitle(), $metadata->title);
        $this->assertEquals($review->getText(), $metadata->description);
        $this->assertEquals(sprintf('/company-reviews/view/%d/', $review->getId()), $metadata->viewUrl);
    }

    private function createCompanyReviewWithText(string $text): CompanyReview
    {
        $companyReviewMock = $this->createMock(CompanyReview::class);

        $companyReviewMock
            ->method('getText')
            ->willReturn($text);
        $companyReviewMock
            ->method('getImages')
            ->willReturn(new ImageCollection());
        $companyReviewMock
            ->method('getVotableId')
            ->willReturn(new VotableIdentifier('1', 'type'));
        $companyReviewMock
            ->method('getCompany')
            ->willReturn($this->company);
        $companyReviewMock
            ->method('getComments')
            ->willReturn(new CommentCollection());
        $companyReviewMock
            ->method('getCommentsWithAnswers')
            ->willReturn(new CommentCollection());

        return $companyReviewMock;
    }
}
