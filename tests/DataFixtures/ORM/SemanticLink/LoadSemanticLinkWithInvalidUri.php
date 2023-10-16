<?php

namespace Tests\DataFixtures\ORM\SemanticLink;

use App\Domain\SemanticLink\Entity\SemanticLink;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Ramsey\Uuid\Uuid;

class LoadSemanticLinkWithInvalidUri extends Fixture
{
    public const REFERENCE_NAME = 'semantic-link-article-with-invalid-uri';

    public function load(ObjectManager $manager): void
    {
        $semanticLink = new SemanticLink(
            Uuid::uuid4(),
            'articles/view/1884/',
            'black orange blue'
        );

        $manager->persist($semanticLink);
        $this->addReference(self::REFERENCE_NAME, $semanticLink);

        $manager->flush();
    }
}
