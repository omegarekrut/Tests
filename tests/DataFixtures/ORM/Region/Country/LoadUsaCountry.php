<?php

namespace Tests\DataFixtures\ORM\Region\Country;

use App\Domain\Region\Entity\Country;
use Doctrine\Persistence\ObjectManager;
use Ramsey\Uuid\Uuid;
use Doctrine\Bundle\FixturesBundle\Fixture;

class LoadUsaCountry extends Fixture
{
    public const REFERENCE_NAME = 'country_usa';

    public function load(ObjectManager $manager): void
    {
        $country = new Country(Uuid::uuid4(), 'USA', 'us');

        $manager->persist($country);
        $manager->flush();

        $this->addReference(self::REFERENCE_NAME, $country);
    }
}
