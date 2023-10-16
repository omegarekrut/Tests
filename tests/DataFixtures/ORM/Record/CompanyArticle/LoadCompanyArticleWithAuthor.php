<?php

namespace Tests\DataFixtures\ORM\Record\CompanyArticle;

use App\Domain\Company\Entity\Company;
use App\Domain\Record\CompanyArticle\Entity\CompanyArticle;
use App\Domain\User\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Generator;
use Tests\DataFixtures\Helper\AuthorHelper;
use Tests\DataFixtures\ORM\Company\Company\LoadAquaMotorcycleShopsCompany;
use Tests\DataFixtures\ORM\SingleReferenceFixtureInterface;
use Tests\DataFixtures\ORM\User\LoadUserWithRealEmail;

class LoadCompanyArticleWithAuthor extends Fixture implements DependentFixtureInterface, FixtureInterface, SingleReferenceFixtureInterface
{
    /** @deprecated Use {@link getReferenceName} */
    public const REFERENCE_NAME = 'company-article-with-author';
    private AuthorHelper $authorHelper;
    private Generator $generator;

    public static function getReferenceName(): string
    {
        return self::REFERENCE_NAME;
    }

    public function __construct(AuthorHelper $authorHelper, Generator $generator)
    {
        $this->authorHelper = $authorHelper;
        $this->generator = $generator;
    }

    public function load(ObjectManager $manager): void
    {
        $company = $this->getReference(LoadAquaMotorcycleShopsCompany::REFERENCE_NAME);
        assert($company instanceof Company);

        $user = $this->getReference(LoadUserWithRealEmail::USER_WITH_REAL_EMAIL);
        assert($user instanceof User);

        $companyArticle = new CompanyArticle(
            $this->generator->realText(20),
            $this->generator->realText(100),
            $this->authorHelper->createFromUser($user),
            $company,
            false,
            $this->generator->realText(100),
        );

        $this->addReference(static::getReferenceName(), $companyArticle);

        $manager->persist($companyArticle);
        $manager->flush();
    }

    /**
     * @return string[]
     */
    public function getDependencies(): array
    {
        return [
            LoadAquaMotorcycleShopsCompany::class,
            LoadUserWithRealEmail::class,
        ];
    }
}
