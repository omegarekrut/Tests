<?php

namespace Tests\DataFixtures\Helper;

use App\Domain\Rating\ValueObject\RatingInfo;
use App\Domain\Rating\VotableRatingInfoAwareInterface;

class RatingHelper
{
    public static function setRating(VotableRatingInfoAwareInterface $votable): void
    {
        $ratingInfo = new RatingInfo(
            random_int(-10, 10),
            random_int(0, 10),
            random_int(0, 10),
            random_int(0, 10)
        );

        $votable->updateRatingInfo($ratingInfo);
    }

    public static function setPositiveRating(VotableRatingInfoAwareInterface $votable): void
    {
        $positiveVotes = random_int(1, 10);

        $ratingInfo = new RatingInfo(
            $positiveVotes,
            $positiveVotes,
            0,
            $positiveVotes
        );

        $votable->updateRatingInfo($ratingInfo);
    }

    public static function setNegativeRating(VotableRatingInfoAwareInterface $votable): void
    {
        $negativeVotes = random_int(1, 10);

        $ratingInfo = new RatingInfo(
            -$negativeVotes,
            0,
            $negativeVotes,
            $negativeVotes
        );

        $votable->updateRatingInfo($ratingInfo);
    }
}
