<?php

namespace Tests\Unit\Domain\User\Normalizer;

use App\Domain\User\Entity\User;
use App\Domain\User\Normalizer\UserLoginNormalizer;
use Tests\Unit\TestCase;

class UserLoginNormalizerTest extends TestCase
{
    public function testNormalizerForAutocommit(): void
    {
        $normalzier = new UserLoginNormalizer();

        $this->assertTrue($normalzier->supportsNormalization($this->mockUser()));
        $this->assertEquals('test', $normalzier->normalize($this->mockUser()));
    }

    private function mockUser(): User
    {
        $mock = $this->createMock(User::class);
        $mock->method('getLogin')
            ->willReturn('test');

        return $mock;
    }
}
