<?php

namespace Tests\DataFixtures\ORM\Record;

use App\Domain\SuggestedNews\Entity\SuggestedNews;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Ramsey\Uuid\Uuid;
use Tests\DataFixtures\Helper\AuthorHelper;
use Tests\DataFixtures\ORM\User\LoadMostActiveUser;
use Tests\DataFixtures\ORM\User\LoadNumberedUsers;

class LoadSuggestedNews extends Fixture implements DependentFixtureInterface
{
    private const REFERENCE_PREFIX = 'suggested-news';
    public const COUNT = 10;

    private \Faker\Generator $generator;
    private AuthorHelper $authorHelper;

    public function __construct(\Faker\Generator $generator, AuthorHelper $authorHelper)
    {
        $this->generator = $generator;
        $this->authorHelper = $authorHelper;
    }

    public static function getRandReferenceName(): string
    {
        return sprintf('%s-%d', self::REFERENCE_PREFIX, rand(1, self::COUNT));
    }

    public function load(ObjectManager $manager): void
    {
        for ($i = 1; $i <= self::COUNT; $i++) {
            $suggestedNews = new SuggestedNews(
                Uuid::uuid4(),
                $this->generator->realText(20),
                $this->generator->realText(),
                $this->authorHelper->chooseAuthor($this),
            );

            $manager->persist($suggestedNews);
            $this->addReference(sprintf('%s-%d', self::REFERENCE_PREFIX, $i), $suggestedNews);
        }

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
