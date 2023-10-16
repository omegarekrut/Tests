<?php

namespace Tests\DataFixtures\ORM\Region\Country;

use App\Domain\Region\Entity\Country;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Ramsey\Uuid\Uuid;

class LoadRussiaCountry extends Fixture
{
    public const REFERENCE_NAME = 'country_russia';

    public function load(ObjectManager $manager): void
    {
        $country = new Country(Uuid::uuid4(), 'Россия', 'ru');

        $manager->persist($country);
        $manager->flush();

        $this->addReference(self::REFERENCE_NAME, $country);
    }
}
