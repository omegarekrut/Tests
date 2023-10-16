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
use Tests\DataFixtures\ORM\Company\Company\LoadCompanyWithoutOwner;
use Tests\DataFixtures\ORM\User\LoadUserWithRealEmail;

class LoadCompanyArticleWithAuthorWithoutCompanyOwner extends Fixture implements DependentFixtureInterface, FixtureInterface
{
    public const REFERENCE_NAME = 'company-article-without-company-owner';
    private AuthorHelper $authorHelper;
    private Generator $generator;

    public function __construct(AuthorHelper $authorHelper, Generator $generator)
    {
        $this->authorHelper = $authorHelper;
        $this->generator = $generator;
    }

    public function load(ObjectManager $manager): void
    {
        $company = $this->getReference(LoadCompanyWithoutOwner::REFERENCE_NAME);
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

        $this->addReference(self::REFERENCE_NAME, $companyArticle);

        $manager->persist($companyArticle);
        $manager->flush();
    }

    /**
     * @return string[]
     */
    public function getDependencies(): array
    {
        return [
            LoadCompanyWithoutOwner::class,
            LoadUserWithRealEmail::class,
        ];
    }
}
