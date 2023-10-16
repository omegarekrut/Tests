<?php

namespace Tests\DataFixtures\ORM\User;

use Doctrine\Bundle\FixturesBundle\Fixture;

abstract class UserFixture extends Fixture
{
    private static int $lastForumUserId = 0;

    protected static function getForumUserId(): int
    {
        return ++self::$lastForumUserId;
    }
}
