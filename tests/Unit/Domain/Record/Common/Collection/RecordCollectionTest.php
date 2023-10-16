<?php

namespace Tests\Unit\Domain\Record\Common\Collection;

use App\Domain\Rating\ValueObject\RatingInfo;
use App\Domain\Record\Common\Collection\RecordCollection;
use App\Domain\Record\Common\Entity\Record;
use Carbon\Carbon;
use Tests\Functional\TestCase;

class RecordCollectionTest extends TestCase
{
    public function testSortByRatingDescAndCreatedAtAsc(): void
    {
        $mustBeFirst = $this->createRecordWithRatingAndCreationDate(10, Carbon::now()->subDays(2));
        $mustBeSecond = $this->createRecordWithRatingAndCreationDate(10, Carbon::now()->subDays(1));
        $mustBeThird = $this->createRecordWithRatingAndCreationDate(9, Carbon::now()->subDays(3));

        $unsortedRecords = new RecordCollection([
            $mustBeThird,
            $mustBeFirst,
            $mustBeSecond,
        ]);
        $this->assertFalse($mustBeFirst === $unsortedRecords->get(0));

        $sortedRecords = $unsortedRecords->sortByRatingDescAndCreatedAtAsc();

        $this->assertEquals($mustBeFirst, $sortedRecords->get(0));
        $this->assertEquals($mustBeSecond, $sortedRecords->get(1));
        $this->assertEquals($mustBeThird, $sortedRecords->get(2));
    }

    private function createRecordWithRatingAndCreationDate(int $rating, Carbon $createdAt): Record
    {
        $ratingInfo = new RatingInfo($rating, $rating, 0, $rating);

        $record = $this->createMock(Record::class);
        $record->method('getRatingInfo')->willReturn($ratingInfo);
        $record->method('getCreatedAt')->willReturn($createdAt);

        return $record;
    }
}
