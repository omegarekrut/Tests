<?php

namespace Tests\DataFixtures\ORM\Region\Country;

use App\Domain\Region\Entity\Country;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Ramsey\Uuid\Uuid;

class LoadNotShowedCountry extends Fixture
{
    public const REFERENCE_NAME = 'country_is_not_showed';

    public function load(ObjectManager $manager): void
    {
        $country = new Country(Uuid::uuid4(), 'XXX', 'xx');
        $country->hide();

        $manager->persist($country);
        $manager->flush();

        $this->addReference(self::REFERENCE_NAME, $country);
    }
}
