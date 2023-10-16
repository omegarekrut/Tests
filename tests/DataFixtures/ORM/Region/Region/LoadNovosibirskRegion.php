<?php

namespace Tests\DataFixtures\ORM\Region\Region;

use App\Domain\Region\Entity\Country;
use App\Domain\Region\Entity\Region;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Ramsey\Uuid\Uuid;
use Tests\DataFixtures\ORM\Region\Country\LoadRussiaCountry;

class LoadNovosibirskRegion extends Fixture implements DependentFixtureInterface
{
    public const REFERENCE_NAME = 'novosibirsk-region';
    public const FAKE_NOVOSIBIRSK_FIAS_ID = '1acfake-3209-fake-b7bf-a509fakecd9';

    public function load(ObjectManager $manager): void
    {
        /** @var Country $country */
        $country = $this->getReference(LoadRussiaCountry::REFERENCE_NAME);
        $region = new Region(
            Uuid::uuid4(),
            $country,
            '54',
            'Новосибирская область',
            'Новосибирская',
            'UTC+7'
        );
        $region->rewriteMappingId(self::FAKE_NOVOSIBIRSK_FIAS_ID);

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
