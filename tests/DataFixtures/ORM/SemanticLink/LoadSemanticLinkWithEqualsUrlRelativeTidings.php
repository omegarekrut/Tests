<?php

namespace Tests\DataFixtures\ORM\SemanticLink;

use App\Domain\Record\Tidings\Entity\Tidings;
use App\Domain\SemanticLink\Entity\SemanticLink;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Tests\DataFixtures\ORM\Record\Tidings\LoadTidingsForSemanticLinks;

class LoadSemanticLinkWithEqualsUrlRelativeTidings extends Fixture implements DependentFixtureInterface
{
    public const REFERENCE_NAME = 'semantic-link-tidings-with-equals_url_relative_tidings';

    private RouterInterface $router;

    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    public function getDependencies(): array
    {
        return [
            LoadTidingsForSemanticLinks::class,
        ];
    }

    public function load(ObjectManager $manager): void
    {
        /** @var Tidings $tidings */
        $tidings = $this->getReference(LoadTidingsForSemanticLinks::REFERENCE_NAME);

        $semanticLink = new SemanticLink(
            Uuid::uuid4(),
            $this->generateTidingsUrl($tidings),
            'hyper black hole отзыв'
        );

        $manager->persist($semanticLink);
        $this->addReference(self::REFERENCE_NAME, $semanticLink);

        $manager->flush();
    }

    public function generateTidingsUrl(Tidings $tidings): string
    {
        return $this->router->generate('tidings_view', ['tidings' => $tidings->getId()], UrlGeneratorInterface::ABSOLUTE_PATH);
    }
}
