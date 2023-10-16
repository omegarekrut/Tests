<?php

namespace Tests\Unit\Domain\User\Entity\ValueObject;

use App\Domain\User\Entity\ValueObject\Rating;
use InvalidArgumentException;
use Tests\Unit\TestCase;

class RatingTest extends TestCase
{
    public function testSetNegativeRatingValue(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Rating must be greater than or equal to zero');

        new Rating(-1);
    }
}
