<?php

namespace Tests\DataFixtures\ORM\Region\Region;

use App\Domain\Region\Entity\Country;
use App\Domain\Region\Entity\Region;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Ramsey\Uuid\Uuid;
use Tests\DataFixtures\ORM\Region\Country\LoadRussiaCountry;

class LoadIrkutskRegion extends Fixture implements DependentFixtureInterface
{
    public const REFERENCE_NAME = 'irkutsk-region';

    public function load(ObjectManager $manager): void
    {
        /** @var Country $country */
        $country = $this->getReference(LoadRussiaCountry::REFERENCE_NAME);
        $region = new Region(
            Uuid::uuid4(),
            $country,
            '38',
            'Иркутская область',
            'Иркутская',
            'UTC+8'
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
            LoadRussiaCountry::class,
        ];
    }
}
