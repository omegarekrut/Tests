<?php

namespace Tests\Unit\Module\Voting\RatingCalculator;

use App\Domain\Rating\Calculator\LikeDislikeRatingCalculator;
use App\Module\Voting\Collection\VoteCollection;
use App\Module\Voting\Entity\VotableIdentifier;
use App\Module\Voting\Entity\Vote;
use App\Module\Voting\VotableInterface;
use App\Module\Voting\VoterInterface;
use Tests\Unit\TestCase;

/**
 * @group rating
 */
class LikeDislikeRatingCalculatorTest extends TestCase
{
    /** @var LikeDislikeRatingCalculator */
    private $ratingCalculator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->ratingCalculator = new LikeDislikeRatingCalculator();
    }

    public function testRatingCalculatedCorrectly(): void
    {
        $votes = new VoteCollection([
            $this->createVoteFromValue(1),
            $this->createVoteFromValue(-1),
            $this->createVoteFromValue(-1),
            $this->createVoteFromValue(1),
            $this->createVoteFromValue(1),
            $this->createVoteFromValue(1),
            $this->createVoteFromValue(-1),
            $this->createVoteFromValue(1),
        ]);

        $ratingInfo = $this->ratingCalculator->calculate($votes);

        $this->assertEquals(8, $ratingInfo->getVotesCount());
        $this->assertEquals(2, $ratingInfo->getRating());
        $this->assertEquals(5, $ratingInfo->getPositiveRating());
        $this->assertEquals(3, $ratingInfo->getNegativeRating());
        $this->assertEquals(count($votes), $ratingInfo->getVotesCount());
    }

    public function testRatingCalculatedCorrectlyOnEmptyCollection(): void
    {
        $votes = new VoteCollection([]);
        $ratingInfo = $this->ratingCalculator->calculate($votes);

        $this->assertEquals(0, $ratingInfo->getVotesCount());
        $this->assertEquals(0, $ratingInfo->getRating());
        $this->assertEquals(0, $ratingInfo->getPositiveRating());
        $this->assertEquals(0, $ratingInfo->getNegativeRating());
    }

    private function createVoteFromValue(int $voteValue): Vote
    {
        $voter = $this->createMock(VoterInterface::class);
        $voter
            ->method('getId')
            ->willReturn(1);

        $votable = $this->createMock(VotableInterface::class);
        $votable
            ->method('getVotableId')
            ->willReturn(new VotableIdentifier('1', 'type'));

        return new Vote($voteValue, $voter, $votable, '127.0.0.1');
    }
}
