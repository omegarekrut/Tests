<?php

namespace Tests\DataFixtures\ORM\Region\Region;

use App\Domain\Region\Entity\Country;
use App\Domain\Region\Entity\Region;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Ramsey\Uuid\Uuid;
use Tests\DataFixtures\ORM\Region\Country\LoadNotShowedCountry;

class LoadRegionFromNotShowedCountry extends Fixture implements DependentFixtureInterface
{
    public const REFERENCE_NAME = 'region-from-hidden-country';

    public function load(ObjectManager $manager): void
    {
        $country = $this->getReference(LoadNotShowedCountry::REFERENCE_NAME);
        assert($country instanceof Country);

        $region = new Region(
            Uuid::uuid4(),
            $country,
            1,
            'test-region',
            'test',
            'UTC+12',
        );

        $manager->persist($region);
        $manager->flush();

        $this->addReference(self::REFERENCE_NAME, $region);
    }

    /**
     * @return string[]
     */
    public function getDependencies(): array
    {
        return [
            LoadNotShowedCountry::class,
        ];
    }
}
