<?php

namespace Tests\Unit\Domain\Record\CompanyReview\Entity;

use App\Domain\Company\Entity\Company;
use App\Domain\Record\CompanyReview\Entity\CompanyReview;
use App\Module\Author\AuthorInterface;
use App\Util\ImageStorage\Image;
use Tests\Functional\TestCase;

class CompanyReviewTest extends TestCase
{
    public function testAddImageForReviewShouldBeAddedToCollection(): void
    {
        $review = $this->createReview();
        $image = $this->createMock(Image::class);

        $review->addImage($image);

        $this->assertCount(1, $review->getImages());
    }

    private function createReview(): CompanyReview
    {
        return new CompanyReview(
            'Some Title',
            'Review text',
            $this->createMock(AuthorInterface::class),
            $this->createMock(Company::class),
        );
    }
}
