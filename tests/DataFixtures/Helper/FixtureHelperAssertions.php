<?php

namespace Tests\DataFixtures\Helper;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use RuntimeException;

final class FixtureHelperAssertions
{
    private function __construct()
    {
    }

    public static function assertFixtureDependsOnOtherFixture(AbstractFixture $fixture, string $fixtureClass): void
    {
        if (!$fixture instanceof DependentFixtureInterface) {
            throw new RuntimeException('Fixture must be dependent');
        }

        if (!in_array($fixtureClass, $fixture->getDependencies())) {
            throw new RuntimeException(sprintf('Fixtures must be dependent on fixtures %s', $fixtureClass));
        }
    }
}
