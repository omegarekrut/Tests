<?php

namespace Tests\DataFixtures\ORM\SuggestedNews;

use App\Domain\SuggestedNews\Entity\SuggestedNews;
use App\Module\Author\AnonymousAuthor;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Generator;
use Ramsey\Uuid\Uuid;
use Tests\DataFixtures\ORM\User\LoadMostActiveUser;
use Tests\DataFixtures\ORM\User\LoadNumberedUsers;

class LoadSuggestedNewsByAnonymousFixture extends Fixture implements DependentFixtureInterface
{
    public const REFERENCE_NAME = 'suggested-news-second';

    private Generator $generator;

    public function __construct(Generator $generator)
    {
        $this->generator = $generator;
    }

    public function load(ObjectManager $manager): void
    {
        $anonymousAuthor = new AnonymousAuthor('Anonymous');

        $suggestedNews = new SuggestedNews(
            Uuid::uuid4(),
            $this->generator->realText(20),
            $this->generator->realText(),
            $anonymousAuthor
        );

        $manager->persist($suggestedNews);
        $this->addReference(self::REFERENCE_NAME, $suggestedNews);

        $manager->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function getDependencies(): array
    {
        return [
            LoadNumberedUsers::class,
            LoadMostActiveUser::class,
        ];
    }
}
