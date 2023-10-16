<?php

namespace Tests\DataFixtures\ORM\Record\Tidings;

use App\Domain\Company\Entity\Company;
use App\Domain\Record\Tidings\Entity\Tidings;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Generator;
use Tests\DataFixtures\Helper\MediaHelper;
use Tests\DataFixtures\Helper\RatingHelper;
use Tests\DataFixtures\ORM\Company\Company\LoadCompanyWithDifferentRecordsByCompanyAuthor;
use Tests\DataFixtures\ORM\Region\Region\LoadTestRegion;

class LoadTidingsByCompanyAuthor extends Fixture implements DependentFixtureInterface
{
    public const REFERENCE_NAME = 'tidings-by-company-author';

    private Generator $generator;
    private MediaHelper $mediaHelper;

    public function __construct(Generator $generator, MediaHelper $mediaHelper)
    {
        $this->generator = $generator;
        $this->mediaHelper = $mediaHelper;
    }

    public function load(ObjectManager $manager): void
    {
        $company = $this->getReference(LoadCompanyWithDifferentRecordsByCompanyAuthor::REFERENCE_NAME);
        assert($company instanceof Company);

        $tidings = new Tidings(
            $this->generator->realText(20),
            $this->generator->realText(100),
            $company->getOwner()
        );

        $tidings->addImage($this->mediaHelper->createImage());
        RatingHelper::setRating($tidings);

        $tidings->setCompanyAuthor($company);

        $manager->persist($tidings);
        $manager->flush();

        $this->addReference(self::REFERENCE_NAME, $tidings);
    }

    /**
     * @return string[]
     */
    public function getDependencies(): array
    {
        return [
            LoadCompanyWithDifferentRecordsByCompanyAuthor::class,
            LoadTestRegion::class,
        ];
    }
}
