<?php

namespace Tests\Unit\Domain\BusinessSubscription\Entity\ValueObject;

use App\Domain\BusinessSubscription\Entity\ValueObject\Price;
use InvalidArgumentException;
use Tests\Unit\TestCase;

class PriceValidationTest extends TestCase
{
    public function testCreatePriceIsPositiveNumber(): void
    {
        $expectedPrice = 100;
        $price = new Price(100);

        self::assertEquals($expectedPrice, $price->getPrice());
    }

    public function testCreatePriceIsNegativeNumberShouldBeException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Price of the tariff should be positive number.');

        new Price(-10);
    }
}
