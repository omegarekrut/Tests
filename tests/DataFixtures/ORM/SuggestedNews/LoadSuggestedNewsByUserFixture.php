<?php

namespace Tests\DataFixtures\ORM\SuggestedNews;

use App\Domain\SuggestedNews\Entity\SuggestedNews;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Generator;
use Ramsey\Uuid\Uuid;
use Tests\DataFixtures\Helper\AuthorHelper;
use Tests\DataFixtures\ORM\User\LoadMostActiveUser;
use Tests\DataFixtures\ORM\User\LoadNumberedUsers;

class LoadSuggestedNewsByUserFixture extends Fixture implements DependentFixtureInterface
{
    public const REFERENCE_NAME = 'suggested-news';

    private Generator $generator;
    private AuthorHelper $authorHelper;

    public function __construct(Generator $generator, AuthorHelper $authorHelper)
    {
        $this->generator = $generator;
        $this->authorHelper = $authorHelper;
    }

    public function load(ObjectManager $manager): void
    {
        $suggestedNews = new SuggestedNews(
            Uuid::uuid4(),
            $this->generator->realText(20),
            $this->generator->realText(),
            $this->authorHelper->chooseAuthor($this),
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
