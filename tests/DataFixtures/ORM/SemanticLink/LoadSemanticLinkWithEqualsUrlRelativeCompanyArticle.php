<?php

namespace Tests\DataFixtures\ORM\SemanticLink;

use App\Domain\Record\CompanyArticle\Entity\CompanyArticle;
use App\Domain\SemanticLink\Entity\SemanticLink;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Tests\DataFixtures\ORM\Record\CompanyArticle\LoadCompanyArticleForSemanticLinks;

class LoadSemanticLinkWithEqualsUrlRelativeCompanyArticle extends Fixture implements DependentFixtureInterface, FixtureInterface
{
    public const REFERENCE_NAME = 'semantic-link-with-equals-url-relative-company-article';

    private RouterInterface $router;

    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    public function load(ObjectManager $manager): void
    {
        /** @var CompanyArticle $companyArticle */
        $companyArticle = $this->getReference(LoadCompanyArticleForSemanticLinks::REFERENCE_NAME);

        $semanticLink = new SemanticLink(
            Uuid::uuid4(),
            $this->generateCompanyArticleUrl($companyArticle),
            'hole black hyper отзыв'
        );

        $manager->persist($semanticLink);
        $this->addReference(self::REFERENCE_NAME, $semanticLink);

        $manager->flush();
    }

    public function generateCompanyArticleUrl(CompanyArticle $companyArticle): string
    {
        return $this->router->generate('company_article_view', ['companyArticle' => $companyArticle->getId()], UrlGeneratorInterface::ABSOLUTE_PATH);
    }

    /**
     * @return string[]
     */
    public function getDependencies(): array
    {
        return [
            LoadCompanyArticleForSemanticLinks::class,
        ];
    }
}
