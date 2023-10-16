<?php

namespace Tests\DataFixtures\ORM\SemanticLink;

use App\Domain\Record\Article\Entity\Article;
use App\Domain\SemanticLink\Entity\SemanticLink;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Tests\DataFixtures\ORM\Record\Articles\LoadArticlesForSemanticLinks;

class LoadSemanticLinkWithEqualsUrlRelativeArticle extends Fixture implements DependentFixtureInterface
{
    public const REFERENCE_NAME = 'semantic-link-article-with-equals_url_relative_article';

    private RouterInterface $router;

    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    public function getDependencies(): array
    {
        return [
            LoadArticlesForSemanticLinks::class,
        ];
    }

    public function load(ObjectManager $manager): void
    {
        /** @var Article $article */
        $article = $this->getReference(LoadArticlesForSemanticLinks::REFERENCE_NAME);

        $semanticLink = new SemanticLink(
            Uuid::uuid4(),
            $this->generateArticleUrl($article),
            'отзыв hole black hyper'
        );

        $manager->persist($semanticLink);
        $this->addReference(self::REFERENCE_NAME, $semanticLink);

        $manager->flush();
    }

    public function generateArticleUrl(Article $article): string
    {
        return $this->router->generate('article_view', ['article' => $article->getId()], UrlGeneratorInterface::ABSOLUTE_PATH);
    }
}
