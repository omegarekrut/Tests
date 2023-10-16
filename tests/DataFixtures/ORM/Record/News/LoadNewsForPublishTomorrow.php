<?php

namespace Tests\DataFixtures\ORM\Record\News;

use App\Domain\Record\News\Entity\News;
use Carbon\Carbon;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Generator;
use Tests\DataFixtures\Helper\MediaHelper;
use Tests\DataFixtures\ORM\User\LoadNumberedUsers;

class LoadNewsForPublishTomorrow extends Fixture implements DependentFixtureInterface
{
    public const REFERENCE_NAME = 'news-for-publish-tomorrow';

    private Generator $faker;
    private MediaHelper $mediaHelper;

    public function __construct(Generator $faker, MediaHelper $mediaHelper)
    {
        $this->faker = $faker;
        $this->mediaHelper = $mediaHelper;
    }

    public function load(ObjectManager $manager): void
    {
        $author = $this->getReference(LoadNumberedUsers::getRandReferenceName());

        $news = new News(
            'Deferred news title',
            $this->faker->realText(),
            $author,
            $this->faker->randomHtml(),
            $this->mediaHelper->createImage()
        );

        $news->rewritePublishAt(Carbon::tomorrow());

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
            LoadNumberedUsers::class,
        ];
    }
}
