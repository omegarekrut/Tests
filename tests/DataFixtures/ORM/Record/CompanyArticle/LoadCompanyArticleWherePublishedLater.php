<?php

namespace Tests\DataFixtures\ORM\Record\CompanyArticle;

use App\Domain\Company\Entity\Company;
use App\Domain\Record\CompanyArticle\Entity\CompanyArticle;
use Carbon\Carbon;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Generator;
use Tests\DataFixtures\Helper\AuthorHelper;
use Tests\DataFixtures\Helper\MediaHelper;
use Tests\DataFixtures\ORM\Company\Company\LoadManySimpleOwnedCompanies;

class LoadCompanyArticleWherePublishedLater extends Fixture implements DependentFixtureInterface, FixtureInterface
{
    /** @deprecated Use {@link getReferenceName} */
    public const REFERENCE_NAME = 'published-later-company-article';

    private Generator $generator;
    private MediaHelper $mediaHelper;
    private AuthorHelper $authorHelper;

    public static function getReferenceName(): string
    {
        return self::REFERENCE_NAME;
    }

    public function __construct(\Faker\Generator $generator, MediaHelper $mediaHelper, AuthorHelper $authorHelper)
    {
        $this->generator = $generator;
        $this->mediaHelper = $mediaHelper;
        $this->authorHelper = $authorHelper;
    }

    public function load(ObjectManager $manager): void
    {
        /** @var Company $company */
        $company = $this->getReference(LoadManySimpleOwnedCompanies::COMPANY_PREFIX_REFERENCE.'-1');

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
        $companyArticle->rewritePublishAt(Carbon::now()->addMonth());

        return $companyArticle;
    }

    /**
     * @return string[]
     */
    public function getDependencies(): array
    {
        return [
            LoadManySimpleOwnedCompanies::class,
        ];
    }
}
