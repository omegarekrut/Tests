<?php

namespace Tests\Functional\Mock;

use App\Domain\User\Entity\User;
use App\Domain\User\Rating\UserRatingCalculatorInterface;
use DatePeriod;

class UserRatingCalculatorMock implements UserRatingCalculatorInterface
{
    private int $ratingForAllPeriod;
    private int $ratingForPeriod;
    private ?DatePeriod $lastCalculatedPeriod;

    public function __construct()
    {
        $this->ratingForAllPeriod = 0;
        $this->ratingForPeriod = 0;
        $this->lastCalculatedPeriod = null;
    }

    public function setReturnedRatingForPeriod(int $rating): void
    {
        $this->ratingForPeriod = $rating;
    }

    public function calculateUserRatingForPeriod(User $user, DatePeriod $period): int
    {
        $this->lastCalculatedPeriod = $period;

        return $this->ratingForPeriod;
    }

    public function getLastCalculatedPeriod(): ?DatePeriod
    {
        return $this->lastCalculatedPeriod;
    }

    public function setReturnedRatingForAllPeriod(int $ratingForAllPeriod): void
    {
        $this->ratingForAllPeriod = $ratingForAllPeriod;
    }

    public function calculateUserRatingForAllPeriod(User $user): int
    {
        return $this->ratingForAllPeriod;
    }
}
