<?php

namespace Tests\Unit\Domain\User\Generator;

use App\Domain\User\Entity\User;
use App\Domain\User\Generator\UsernameByEmailGenerator;
use App\Domain\User\Repository\UserRepository;
use Tests\Unit\TestCase;

/**
 * @group user-generator
 * @group oauth
 */
class UsernameByEmailGeneratorTest extends TestCase
{
    private const EXISTS_USERNAME = 'exists_username1';
    private const EXISTS_USERNAME2 = 'exists_username2';
    private const USER_NAME_AFTER_GENERATION = 'exists_username3';

    /** @var UsernameByEmailGenerator */
    private $generator;

    protected function setUp(): void
    {
        parent::setUp();

        $userRepository = $this->createUserRepository([self::EXISTS_USERNAME, self::EXISTS_USERNAME2]);
        $this->generator = new UsernameByEmailGenerator($userRepository);
    }

    public function testGenerate(): void
    {
        $this->assertEquals('username', $this->generator->generate('username@email.com'));
    }

    public function testEnlargingLengthString(): void
    {
        $username = $this->generator->generate('u@email.com');
        $this->assertStringContainsString('u', $username);
        $this->assertGreaterThanOrEqual(3, mb_strlen($username));
    }

    public function testReducingLengthString(): void
    {
        $username = $this->generator->generate('string_of_34_characters_fishingsib@email.com');
        $this->assertStringContainsString('string_of_34', $username);
        $this->assertLessThanOrEqual(30, mb_strlen($username));
    }

    public function testModifyStringForExistsUsername(): void
    {
        $username = $this->generator->generate(self::EXISTS_USERNAME.'@email.com');

        $this->assertEquals(self::USER_NAME_AFTER_GENERATION, $username);
        $this->assertGreaterThanOrEqual(3, mb_strlen($username));
        $this->assertLessThanOrEqual(30, mb_strlen($username));
    }

    public function testGenerationForInvalidEmail(): void
    {
        $this->assertEquals('username', $this->generator->generate('username'));

        $usernameFromEmptyEmail = $this->generator->generate(null);
        $this->assertGreaterThanOrEqual(3, mb_strlen($usernameFromEmptyEmail));
        $this->assertLessThanOrEqual(30, mb_strlen($usernameFromEmptyEmail));
    }

    private function createUserRepository(array $usernames): UserRepository
    {
        $user = $this->createMock(User::class);
        $userRepository = $this->createMock(UserRepository::class);
        $userRepository
            ->method('findOneByLoginOrEmail')
            ->willReturnCallback(function (string $loginOrEmail) use ($user, $usernames) {
                return in_array($loginOrEmail, $usernames) ? $user : null;
            });

        return $userRepository;
    }
}
