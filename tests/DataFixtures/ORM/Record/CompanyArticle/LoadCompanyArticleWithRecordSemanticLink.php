<?php

namespace Tests\DataFixtures\ORM\Record\CompanyArticle;

use App\Domain\Record\Common\Entity\RecordSemanticLink;
use App\Domain\Record\CompanyArticle\Entity\CompanyArticle;
use App\Domain\SemanticLink\Entity\SemanticLink;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Ramsey\Uuid\Uuid;
use Tests\DataFixtures\ORM\SemanticLink\LoadSemanticLinkWithValidUri;

class LoadCompanyArticleWithRecordSemanticLink extends Fixture implements DependentFixtureInterface, FixtureInterface
{
    public const REFERENCE_NAME = 'company-article-with-semantic-links';

    public function load(ObjectManager $manager): void
    {
        /** @var CompanyArticle $companyArticle */
        $companyArticle = $this->getReference(LoadCompanyArticleForSemanticLinks::REFERENCE_NAME);

        /** @var SemanticLink $semanticLink */
        $semanticLink = $this->getReference(LoadSemanticLinkWithValidUri::REFERENCE_NAME);

        $recordSemanticLink = self::createRecordSemanticLink($companyArticle, $semanticLink);

        $companyArticle->attachUniqueRecordSemanticLink($recordSemanticLink);

        $this->addReference(self::REFERENCE_NAME, $companyArticle);

        $manager->flush();
    }

    private static function createRecordSemanticLink(CompanyArticle $companyArticle, SemanticLink $semanticLink): RecordSemanticLink
    {
        return new RecordSemanticLink(
            Uuid::uuid4(),
            $companyArticle,
            $semanticLink,
            $semanticLink->getText()
        );
    }

    /**
     * @return string[]
     */
    public function getDependencies(): array
    {
        return [
            LoadCompanyArticleForSemanticLinks::class,
            LoadSemanticLinkWithValidUri::class,
        ];
    }
}
