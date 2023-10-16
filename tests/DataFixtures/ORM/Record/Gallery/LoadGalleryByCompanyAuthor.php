<?php

namespace Tests\DataFixtures\ORM\Record\Gallery;

use App\Domain\Category\Entity\Category;
use App\Domain\Company\Entity\Company;
use App\Domain\Record\Gallery\Entity\Gallery;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Generator;
use Tests\DataFixtures\Helper\MediaHelper;
use Tests\DataFixtures\ORM\Company\Company\LoadCompanyWithDifferentRecordsByCompanyAuthor;
use Tests\DataFixtures\ORM\LoadCategories;

class LoadGalleryByCompanyAuthor extends Fixture implements DependentFixtureInterface
{
    public const REFERENCE_NAME = 'gallery-by-company-author';

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

        $category = $this->getReference(LoadCategories::getRandReferenceNameForRootCategory(LoadCategories::ROOT_GALLERY));
        assert($category instanceof Category);

        $gallery = new Gallery(
            $this->generator->realText(20),
            $this->generator->realText(100),
            $company->getOwner(),
            $category,
            $this->mediaHelper->createImage()
        );

        $gallery->setCompanyAuthor($company);

        $manager->persist($gallery);
        $manager->flush();

        $this->addReference(self::REFERENCE_NAME, $gallery);
    }

    /**
     * @return string[]
     */
    public function getDependencies(): array
    {
        return [
            LoadCategories::class,
            LoadCompanyWithDifferentRecordsByCompanyAuthor::class,
        ];
    }
}
