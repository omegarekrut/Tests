<?php

namespace Tests\Unit\Twig\User;

use App\Domain\User\Entity\User;
use App\Module\Author\AuthorFactory;
use App\Twig\User\ObjectIsUserAssertion;
use stdClass;
use Tests\Unit\TestCase;

/**
 * group twig
 */
class ObjectIsUserAssertionTest extends TestCase
{
    private ObjectIsUserAssertion $objectIsUserAssertion;

    protected function setUp(): void
    {
        parent::setUp();

        $this->objectIsUserAssertion = new ObjectIsUserAssertion();
    }

    protected function tearDown(): void
    {
        unset($this->objectIsUserAssertion);

        parent::tearDown();
    }

    public function testAssertionPassedForUser(): void
    {
        $user = $this->createMock(User::class);

        $this->assertTrue(($this->objectIsUserAssertion)($user));
    }

    public function testAssertionFailedForAnonymousAuthor(): void
    {
        $anonymousAuthor = AuthorFactory::createAnonymousFromUsername('username');

        $this->assertFalse(($this->objectIsUserAssertion)($anonymousAuthor));
    }

    public function testAssertionFailedForNotUser(): void
    {
        $notUser = new stdClass();

        $this->assertFalse(($this->objectIsUserAssertion)($notUser));
    }
}
