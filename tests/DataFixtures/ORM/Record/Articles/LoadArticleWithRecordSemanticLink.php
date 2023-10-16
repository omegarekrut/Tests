<?php

namespace Tests\DataFixtures\ORM\Record\Articles;

use App\Domain\Record\Article\Entity\Article;
use App\Domain\Record\Common\Entity\RecordSemanticLink;
use App\Domain\SemanticLink\Entity\SemanticLink;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Ramsey\Uuid\Uuid;
use Tests\DataFixtures\ORM\SemanticLink\LoadSemanticLinkWithValidUri;

class LoadArticleWithRecordSemanticLink extends Fixture implements DependentFixtureInterface
{
    public const REFERENCE_NAME = 'article-with-semantic-links';

    public function getDependencies(): array
    {
        return [
            LoadArticlesForSemanticLinks::class,
            LoadSemanticLinkWithValidUri::class,
        ];
    }

    public function load(ObjectManager $manager): void
    {
        /** @var Article $article */
        $article = $this->getReference(LoadArticlesForSemanticLinks::REFERENCE_NAME);

        /** @var SemanticLink $semanticLink */
        $semanticLink = $this->getReference(LoadSemanticLinkWithValidUri::REFERENCE_NAME);

        $recordSemanticLink = self::createRecordSemanticLink($article, $semanticLink);

        $article->attachUniqueRecordSemanticLink($recordSemanticLink);

        $this->addReference(self::REFERENCE_NAME, $article);

        $manager->flush();
    }

    private static function createRecordSemanticLink(Article $article, SemanticLink $semanticLink): RecordSemanticLink
    {
        return new RecordSemanticLink(
            Uuid::uuid4(),
            $article,
            $semanticLink,
            $semanticLink->getText()
        );
    }
}
