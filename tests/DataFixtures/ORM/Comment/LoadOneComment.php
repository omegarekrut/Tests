<?php

namespace Tests\DataFixtures\ORM\Comment;

use App\Domain\Comment\Entity\Comment;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Faker\Generator;
use Ramsey\Uuid\Uuid;
use Tests\DataFixtures\Helper\AuthorHelper;
use Tests\DataFixtures\ORM\Record\LoadNews;

class LoadOneComment extends Fixture implements DependentFixtureInterface
{
    private Generator $generator;
    private AuthorHelper $authorHelper;

    public function __construct(\Faker\Generator $generator, AuthorHelper $authorHelper)
    {
        $this->generator = $generator;
        $this->authorHelper = $authorHelper;
    }

    public function load(ObjectManager $manager): void
    {
        $record = $this->getReference(LoadNews::getRandReferenceName());

        $author = $this->authorHelper->createAnonymousFromUsername();

        $comment = new Comment(Uuid::uuid4(), $this->generator->regexify('[A-Za-z0-9]{20}'), 'Comment containing link http://google.com', $record, $author);

        $manager->persist($comment);
        $manager->flush();
    }

    /**
     * @inheritDoc
     */
    public function getDependencies(): array
    {
        return [
            LoadNews::class,
        ];
    }
}
