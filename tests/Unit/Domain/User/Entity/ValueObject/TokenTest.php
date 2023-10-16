<?php

namespace Tests\Unit\Domain\User\Entity\ValueObject;

use App\Domain\User\Entity\ValueObject\Token;
use Carbon\Carbon;
use Tests\Unit\TestCase;

/**
 * @group reset-password
 * @group public-email
 */
class TokenTest extends TestCase
{
    public function testGetTokenAgeInMinutesWithoutToken(): void
    {
        $token = new Token();

        $this->assertEquals(null, $token->getTokenAgeInMinutes());
    }

    public function testGetTokenAgeInMinutes(): void
    {
        $token = new Token('token', Carbon::now());

        $this->assertEquals(0, $token->getTokenAgeInMinutes());
    }

    public function testIsValidTokenWithoutToken(): void
    {
        $token = new Token();

        $this->assertEquals(false, $token->isValidToken());
    }

    public function testIsValidTokenWithoutDate(): void
    {
        $token = new Token('token');

        $this->assertEquals(false, $token->isValidToken());
    }

    public function testIsValidTokenWithNotExpiredToken(): void
    {
        $token = new Token('token', Carbon::now());

        $this->assertEquals(true, $token->isValidToken());
    }

    public function testIsValidTokenWithExpiredToken(): void
    {
        $token = new Token('token', Carbon::create('2010', '01', '01'));

        $this->assertEquals(false, $token->isValidToken());
    }
}
