<?php

namespace Tests\DataFixtures\ORM\SemanticLink;

use App\Domain\SemanticLink\Entity\SemanticLink;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Ramsey\Uuid\Uuid;

class LoadSemanticLinkWithValidUri extends Fixture
{
    public const REFERENCE_NAME = 'semantic-link-article-with-valid-uri';

    public function load(ObjectManager $manager): void
    {
        $semanticLink = new SemanticLink(
            Uuid::uuid4(),
            '/articles/view/86281/',
            'black hole hyper отзыв'
        );

        $manager->persist($semanticLink);
        $this->addReference(self::REFERENCE_NAME, $semanticLink);

        $manager->flush();
    }
}
