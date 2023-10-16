<?php

namespace Tests\Unit\Domain\User\Entity;

use Generator;
use InvalidArgumentException;
use Tests\Traits\UserGeneratorTrait;
use Tests\Unit\TestCase;

class UserTest extends TestCase
{

    use UserGeneratorTrait;

    private $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = $this->generateUser();
    }

    protected function tearDown(): void
    {
        unset($this->user);

        parent::tearDown();
    }

    public function testUpdateActivityRating(): void
    {
        $this->user->updateActivityRating(0);
        $this->assertEquals(0, $this->user->getActivityRating()->getValue());

        $this->user->updateActivityRating(1);
        $this->assertEquals(1, $this->user->getActivityRating()->getValue());
    }

    public function testUpdateGlobalRating(): void
    {
        $this->user->updateGlobalRating(0);
        $this->assertEquals(0, $this->user->getGlobalRating()->getValue());

        $this->user->updateGlobalRating(1);
        $this->assertEquals(1, $this->user->getGlobalRating()->getValue());
    }

    public function testNegativeUpdateActivityRating(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Rating must be greater than or equal to zero');

        $this->user->updateActivityRating(-1);
    }

    public function testNegativeUpdateGlobalRating(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Rating must be greater than or equal to zero');

        $this->user->updateGlobalRating(-1);
    }

    /**
     * @dataProvider getUserRoles
     */
    public function testHasRoleAdmin(string $userRole, bool $expectedStatus): void
    {
        $this->user->rewriteGroup($userRole);

        $this->assertTrue($expectedStatus === $this->user->hasAdminRole());
    }

    public function getUserRoles(): Generator
    {
        yield ['moderator_abm', false];

        yield ['moderator', false];

        yield ['admin', true];

        yield ['user', false];
    }
}
