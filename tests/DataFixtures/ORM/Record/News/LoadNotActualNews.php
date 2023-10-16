<?php

namespace Tests\DataFixtures\ORM\Record\News;

use App\Domain\Record\News\Entity\News;
use Carbon\Carbon;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Generator;
use Tests\DataFixtures\Helper\MediaHelper;
use Tests\DataFixtures\ORM\User\LoadTestUser;

class LoadNotActualNews extends Fixture implements DependentFixtureInterface
{
    public const REFERENCE_NAME = 'not-actual-news';

    private Generator $faker;
    private MediaHelper $mediaHelper;

    public function __construct(Generator $faker, MediaHelper $mediaHelper)
    {
        $this->faker = $faker;
        $this->mediaHelper = $mediaHelper;
    }

    public function load(ObjectManager $manager): void
    {
        $author = $this->getReference(LoadTestUser::USER_TEST);

        $news = new News(
            'Not actual news',
            $this->faker->realText(),
            $author,
            $this->faker->randomHtml(),
            $this->mediaHelper->createImage()
        );

        $news->updateCreatedAt(Carbon::now()->subWeeks(2)->subMinute());

        $manager->persist($news);
        $manager->flush();

        $this->addReference(self::REFERENCE_NAME, $news);
    }

    /**
     * @return string[]
     */
    public function getDependencies(): array
    {
        return [
            LoadTestUser::class,
        ];
    }
}
