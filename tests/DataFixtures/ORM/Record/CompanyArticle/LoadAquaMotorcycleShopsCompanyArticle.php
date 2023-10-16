<?php

namespace Tests\DataFixtures\ORM\Record\CompanyArticle;

use App\Domain\Company\Entity\Company;
use App\Domain\Record\CompanyArticle\Entity\CompanyArticle;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Generator;
use Ramsey\Uuid\Uuid;
use Tests\DataFixtures\Helper\AuthorHelper;
use Tests\DataFixtures\Helper\MediaHelper;
use Tests\DataFixtures\ORM\Company\Company\LoadAquaMotorcycleShopsCompany;
use Tests\DataFixtures\ORM\User\LoadMostActiveUser;
use Tests\DataFixtures\ORM\User\LoadNumberedUsers;

class LoadAquaMotorcycleShopsCompanyArticle extends Fixture implements DependentFixtureInterface, FixtureInterface
{
    /** @deprecated Use {@link getReferenceName} */
    public const REFERENCE_NAME = 'aqua-motorcycle-shops-company-article';

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
        $company = $this->getReference(LoadAquaMotorcycleShopsCompany::REFERENCE_NAME);

        $companyArticle = $this->createCompanyArticle($company);

        $manager->persist($companyArticle);

        for ($i = 0; $i < 5; ++$i) {
            $companyArticle->addComment(
                Uuid::uuid4(),
                $this->generator->regexify('[A-Za-z0-9]{20}'),
                $this->generator->realText(),
                $this->authorHelper->chooseAuthor($this)
            );
        }

        $manager->flush();
        $this->addReference(self::REFERENCE_NAME, $companyArticle);
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
            LoadAquaMotorcycleShopsCompany::class,
            LoadMostActiveUser::class,
            LoadNumberedUsers::class,
        ];
    }
}
