<?php

namespace Tests\DataFixtures\ORM\Record\CompanyReview;

use App\Domain\Company\Entity\Company;
use App\Domain\Record\CompanyReview\Entity\CompanyReview;
use App\Module\Author\AuthorInterface;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Generator;
use Tests\DataFixtures\Helper\Factory\CompanyReviewFakeFactory;
use Tests\DataFixtures\Helper\MediaHelper;
use Tests\DataFixtures\ORM\Company\Company\LoadAquaMotorcycleShopsCompany;
use Tests\DataFixtures\ORM\User\LoadUserWithAvatar;

class LoadCompanyReviews extends Fixture implements DependentFixtureInterface, FixtureInterface
{
    public const REFERENCE_NAME = 'aqua-motorcycle-shops-company-review';
    private MediaHelper $mediaHelper;
    private CompanyReviewFakeFactory $companyReviewFakeFactory;
    private Generator $faker;

    public function __construct(MediaHelper $mediaHelper, CompanyReviewFakeFactory $companyReviewFakeFactory, Generator $faker)
    {
        $this->mediaHelper = $mediaHelper;
        $this->companyReviewFakeFactory = $companyReviewFakeFactory;
        $this->faker = $faker;
    }

    public function load(ObjectManager $manager): void
    {
        $company = $this->getReference(LoadAquaMotorcycleShopsCompany::REFERENCE_NAME);
        assert($company instanceof Company);

        $author = $this->getReference(LoadUserWithAvatar::REFERENCE_NAME);
        assert($author instanceof AuthorInterface);

        $companyReview = $this->createCompanyReview($company, $author);
        $this->addReference(self::REFERENCE_NAME, $companyReview);

        $manager->persist($companyReview);
        $manager->flush();
    }

    public function createCompanyReview(Company $company, AuthorInterface $author): CompanyReview
    {
        $companyReview = $this->companyReviewFakeFactory->createFake($company, $author, $this->faker->realText());
        $companyReview->addImage($this->mediaHelper->createImage());

        return $companyReview;
    }

    /**
     * @return string[]
     */
    public function getDependencies(): array
    {
        return [
            LoadAquaMotorcycleShopsCompany::class,
            LoadUserWithAvatar::class,
        ];
    }
}
