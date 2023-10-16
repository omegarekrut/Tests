<?php

namespace Tests\DataFixtures\ORM\Region\Region;

use App\Domain\Region\Entity\Country;
use App\Domain\Region\Entity\Region;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Ramsey\Uuid\Uuid;
use Tests\DataFixtures\ORM\Region\Country\LoadUsaCountry;

class LoadTestRegion extends Fixture implements DependentFixtureInterface
{
    public const REFERENCE_NAME = 'region';
    public const REGION_NAME = 'test-region';

    public function load(ObjectManager $manager): void
    {
        $country = $this->getReference(LoadUsaCountry::REFERENCE_NAME);
        assert($country instanceof Country);

        $region = new Region(
            Uuid::uuid4(),
            $country,
            1,
            'test-region',
            'test-reg',
            'UTC+1',
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
            LoadUsaCountry::class,
        ];
    }
}
