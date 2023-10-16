<?php

namespace Tests\Unit\Domain\Rating\RatingCalculator;

use App\Domain\Category\Entity\Category;
use App\Domain\Rating\Calculator\RecordAggregateRatingCalculator;
use App\Domain\Rating\ValueObject\RatingInfo;
use App\Domain\Record\Common\Repository\RecordRepository;
use Tests\Unit\TestCase;

class RecordAggregateRatingCalculatorTest extends TestCase
{
    private $repositoryMock;
    private $calculator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repositoryMock = $this->createMock(RecordRepository::class);
        $this->calculator = new RecordAggregateRatingCalculator($this->repositoryMock);
    }

    protected function tearDown(): void
    {
        unset(
            $this->repositoryMock,
            $this->calculator
        );

        parent::tearDown();
    }

    public function testVotesCountIsNull(): void
    {
        $actual = $this->calculator->calculate(
            new RatingInfo(0, 0, 0, 0),
            $this->createMock(Category::class)
        );

        $this->assertSame('0', $actual);
    }

    public function testCorrectCalculate(): void
    {
        $this->repositoryWillReturn(120, 10);

        $actual = $this->calculator->calculate(
            $this->createPositiveRatingInfo(5),
            $this->createMock(Category::class)
        );

        $this->assertSame('2.1', $actual);
    }

    public function testPositiveRecordsCountIsNull(): void
    {
        $this->repositoryWillReturn(120, 0);

        $actual = $this->calculator->calculate(
            $this->createPositiveRatingInfo(3),
            $this->createMock(Category::class)
        );

        $this->assertSame('5', $actual);
    }

    public function testCategoryDoesntHaveVotes(): void
    {
        $this->repositoryWillReturn(0, 0);

        $actual = $this->calculator->calculate(
            $this->createPositiveRatingInfo(3),
            $this->createMock(Category::class)
        );

        $this->assertSame('5', $actual);
    }

    private function createPositiveRatingInfo(int $positiveRating): RatingInfo
    {
        return new RatingInfo($positiveRating, $positiveRating, 0, $positiveRating);
    }

    private function repositoryWillReturn(int $votesCount, int $positiveRecordsCount): void
    {
        $this->repositoryMock
            ->method('getSumVotesByCategory')
            ->willReturn($votesCount);

        $this->repositoryMock
            ->method('getCountWithPositiveRatingByCategory')
            ->willReturn($positiveRecordsCount);
    }
}
