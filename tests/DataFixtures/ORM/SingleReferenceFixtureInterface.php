<?php

namespace Tests\DataFixtures\ORM;

use Doctrine\Bundle\FixturesBundle\ORMFixtureInterface;

interface SingleReferenceFixtureInterface extends ORMFixtureInterface
{
    public static function getReferenceName(): string;
}
