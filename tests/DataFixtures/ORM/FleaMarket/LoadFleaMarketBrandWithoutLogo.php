<?php

namespace Tests\DataFixtures\ORM\FleaMarket;

use App\Domain\FleaMarket\Entity\Brand;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Ramsey\Uuid\Uuid;

class LoadFleaMarketBrandWithoutLogo extends Fixture
{
    public const REFERENCE_NAME = 'flea-market-brand-without-logo';

    public function load(ObjectManager $manager): void
    {
        $brand = new Brand(
            Uuid::uuid4(),
            'Tamuro',
            'Приманки умеренной ценовой категории, с достаточным уровнем качества. Компания производит приманки в Корее. 
            Утверждается, что при разработке воблеров использовался опыт известных рыболовов-спортсменов.',
            'tamuro'
        );

        $manager->persist($brand);
        $manager->flush();

        $this->addReference(self::REFERENCE_NAME, $brand);
    }
}
