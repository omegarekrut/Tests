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
use Tests\DataFixtures\ORM\Company\CompanyWithSubscription\LoadCompanyWithPremiumSubscription;
use Tests\DataFixtures\ORM\SingleReferenceFixtureInterface;

class LoadArticleOfCompanyWithPremiumSubscription extends Fixture implements DependentFixtureInterface, FixtureInterface, SingleReferenceFixtureInterface
{
    /** @deprecated Use {@link getReferenceName} */
    public const REFERENCE_NAME = 'company-article-with-premium-subscription';

    private AuthorHelper $authorHelper;
    private Generator $generator;

    public static function getReferenceName(): string
    {
        return self::REFERENCE_NAME;
    }

    public function __construct(AuthorHelper $authorHelper, \Faker\Generator $generator)
    {
        $this->authorHelper = $authorHelper;
        $this->generator = $generator;
    }

    public function load(ObjectManager $manager): void
    {
        $company = $this->getReference(LoadCompanyWithPremiumSubscription::getReferenceName());
        assert($company instanceof Company);

        $companyArticle = new CompanyArticle(
            $this->generator->realText(20),
            'Lorem ipsum dolor sit amet, black hole hyper отзыв перейти consectetur adipiscing elit. Morbi convallis sagittis bibendum.',
            $this->authorHelper->createFromUser($company->getOwner()),
            $company,
            false,
            'Lorem ipsum dolor sit amet, black hole hyper отзыв перейти consectetur adipiscing elit. Morbi convallis sagittis bibendum.',
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
            LoadCompanyWithPremiumSubscription::class,
        ];
    }
}
