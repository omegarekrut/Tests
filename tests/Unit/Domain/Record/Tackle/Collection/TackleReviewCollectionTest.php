<?php

namespace Tests\Unit\Domain\Record\Tackle\Collection;

use App\Domain\Record\Tackle\Collection\TackleReviewCollection;
use App\Domain\Record\Tackle\Entity\TackleReview;
use Carbon\Carbon;
use Tests\Unit\TestCase;

class TackleReviewCollectionTest extends TestCase
{
    public function testOrderByCreatedAtAsc(): void
    {
        $reviewOne = $this->createTackleReviewWithCreatedAt(Carbon::now());
        $reviewTwo = $this->createTackleReviewWithCreatedAt(Carbon::now()->addDay());
        $reviewThree = $this->createTackleReviewWithCreatedAt(Carbon::now()->subDay());

        $tackleReviews = new TackleReviewCollection([$reviewOne, $reviewTwo, $reviewThree]);
        $sortedTackleReviews = $tackleReviews->orderByCreatedAtAsc()->getValues();

        $this->assertTrue($sortedTackleReviews[0] === $reviewThree);
        $this->assertTrue($sortedTackleReviews[1] === $reviewOne);
        $this->assertTrue($sortedTackleReviews[2] === $reviewTwo);
    }

    private function createTackleReviewWithCreatedAt(\DateTime $createdAt): TackleReview
    {
        $stub = $this->createMock(TackleReview::class);
        $stub
            ->method('getCreatedAt')
            ->willReturn($createdAt);

        return $stub;
    }
}
