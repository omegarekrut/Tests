<?php

namespace Tests\DataFixtures\ORM\Record\CompanyArticle;

use App\Domain\Company\Entity\Company;
use App\Domain\Record\CompanyArticle\Entity\CompanyArticle;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Generator;
use Tests\DataFixtures\Helper\AuthorHelper;
use Tests\DataFixtures\Helper\MediaHelper;
use Tests\DataFixtures\ORM\Company\Company\LoadPaidReservoirsCompany;

class LoadPaidReservoirsCompanyArticle extends Fixture implements DependentFixtureInterface, FixtureInterface
{
    public const REFERENCE_NAME = 'paid-reservoirs-company-article';
    private MediaHelper $mediaHelper;
    private AuthorHelper $authorHelper;
    private Generator $generator;

    public function __construct(MediaHelper $mediaHelper, AuthorHelper $authorHelper, \Faker\Generator $generator)
    {
        $this->mediaHelper = $mediaHelper;
        $this->authorHelper = $authorHelper;
        $this->generator = $generator;
    }

    public function load(ObjectManager $manager): void
    {
        /** @var Company $company */
        $company = $this->getReference(LoadPaidReservoirsCompany::REFERENCE_NAME);

        $companyArticle = $this->createCompanyArticle($company);
        $this->addReference(self::REFERENCE_NAME, $companyArticle);

        $manager->persist($companyArticle);
        $manager->flush();
    }

    public function createCompanyArticle(Company $company): CompanyArticle
    {
        $companyArticle = new CompanyArticle(
            $this->generator->realText(20),
            $this->generator->randomBBCode(),
            $this->authorHelper->createFromUser($company->getOwner()),
            $company,
            false,
            $this->generator->realText(255)
        );
        $companyArticle->addImage($this->mediaHelper->createImage());
        $companyArticle->addVideoUrl($this->generator->videoUrl());

        return $companyArticle;
    }

    /**
     * @return string[]
     */
    public function getDependencies(): array
    {
        return [
            LoadPaidReservoirsCompany::class,
        ];
    }
}
