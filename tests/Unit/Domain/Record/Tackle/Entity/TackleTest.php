<?php

namespace Tests\Unit\Domain\Record\Tackle\Entity;

use App\Domain\Category\Entity\Category;
use App\Domain\Record\Tackle\Entity\Tackle;
use App\Domain\Record\Tackle\Entity\TackleBrand;
use App\Domain\Record\Tackle\Entity\TackleReview;
use App\Module\Author\AuthorInterface;
use Carbon\Carbon;
use Tests\Unit\TestCase;

/**
 * @group tackle
 * @group entity
 */
class TackleTest extends TestCase
{
    public function testUpdateLastReviewInformationAfterAddReview(): void
    {
        $tackle = $this->getTackle();

        $this->assertEmpty($tackle->getLastReview());

        $tackleReviewDate = Carbon::now();

        $tackle->addReview($this->getTackleReview($tackleReviewDate));

        $this->assertNotEmpty($tackle->getLastReview());
        $this->assertEquals($tackleReviewDate, $tackle->getLastReview()->getCreatedAt());
    }

    public function testChangeLastReviewOnLastTackleReviewFromCollection(): void
    {
        $tackle = $this->getTackle();

        $tackleReviewDate = Carbon::now();

        $firstReview = $this->getTackleReview($tackleReviewDate);
        $secondReview = $this->getTackleReview((clone $tackleReviewDate)->addDay());
        $thirdReview = $this->getTackleReview((clone $tackleReviewDate)->subDay());

        $tackle->addReview($firstReview);
        $tackle->addReview($secondReview);
        $tackle->addReview($thirdReview);

        $this->assertEquals($secondReview->getCreatedAt(), $tackle->getLastReview()->getCreatedAt());

        $tackle->removeReview($tackle->getLastReview());

        $this->assertEquals($firstReview->getCreatedAt(), $tackle->getLastReview()->getCreatedAt());
    }

    public function testClearLastReviewInformationAfterDeleteOnlyReview(): void
    {
        $tackle = $this->getTackle();

        $tackleReviewDate = Carbon::now();

        $review = $this->getTackleReview($tackleReviewDate);

        $tackle->addReview($review);

        $this->assertNotEmpty($tackle->getLastReview());

        $tackle->removeReview($review);

        $this->assertEmpty($tackle->getLastReview());
    }

    private function getTackle(): Tackle
    {
        return new Tackle(
            'Title',
            'description',
            $this->createMock(AuthorInterface::class),
            $this->createMock(Category::class),
            $this->createMock(TackleBrand::class)
        );
    }

    private function getTackleReview(\DateTime $createdDate): TackleReview
    {
        $tackleReview = $this->createMock(TackleReview::class);
        $tackleReview->method('getCreatedAt')
            ->willReturn($createdDate);

        return $tackleReview;
    }
}
