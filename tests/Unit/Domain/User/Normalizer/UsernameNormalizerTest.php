<?php

namespace Tests\Unit\Domain\User\Normalizer;

use App\Domain\User\Entity\User;
use App\Domain\User\Normalizer\UsernameNormalizer;
use Tests\Unit\TestCase;

/**
 * @group Usernames
 */
class UsernameNormalizerTest extends TestCase
{
    private UsernameNormalizer $usernameNormalizer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->usernameNormalizer = new UsernameNormalizer();
    }

    public function testNormalizeWithEmptyArray(): void
    {
        $usernames = [];

        $expectedNormalizedData = [
            'usernames' => [],
        ];

        $this->assertEquals($expectedNormalizedData, $this->usernameNormalizer->normalize($usernames));
    }

    public function testNormalize(): void
    {
        $usernames = [
            $this->getMockUser('login1'),
            $this->getMockUser('login2'),
        ];

        $expectedNormalizedData = [
            'usernames' => [
                ['username' => 'login1'],
                ['username' => 'login2'],
            ],
        ];

        $this->assertEquals($expectedNormalizedData, $this->usernameNormalizer->normalize($usernames));
    }

    private function getMockUser(string $login): User
    {
        $mock = $this->createMock(User::class);

        $mock
            ->method('getLogin')
            ->willReturn($login);

        return $mock;
    }
}
