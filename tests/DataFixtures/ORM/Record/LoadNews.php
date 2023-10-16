<?php

namespace Tests\DataFixtures\ORM\Record;

use App\Domain\Record\News\Entity\News;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Tests\DataFixtures\Helper\AuthorHelper;
use Tests\DataFixtures\Helper\MediaHelper;
use Tests\DataFixtures\ORM\User\LoadMostActiveUser;
use Tests\DataFixtures\ORM\User\LoadNumberedUsers;

class LoadNews extends Fixture implements DependentFixtureInterface
{
    private const REFERENCE_PREFIX = 'news';
    public const COUNT = 30;

    private \Faker\Generator $generator;
    private AuthorHelper $authorHelper;
    private MediaHelper $mediaHelper;

    public function __construct(\Faker\Generator $generator, AuthorHelper $authorHelper, MediaHelper $mediaHelper)
    {
        $this->generator = $generator;
        $this->authorHelper = $authorHelper;
        $this->mediaHelper = $mediaHelper;
    }

    public static function getRandReferenceName(): string
    {
        return sprintf('%s-%d', self::REFERENCE_PREFIX, rand(1, self::COUNT));
    }

    public function load(ObjectManager $manager): void
    {
        for ($i = 1; $i <= self::COUNT; $i++) {
            $news = new News(
                $this->generator->realText(20),
                $this->generator->realText(),
                $this->authorHelper->chooseAuthor($this),
                $this->generator->randomHtml(),
                $this->mediaHelper->createImage()
            );

            $manager->persist($news);
            $this->addReference(sprintf('%s-%d', self::REFERENCE_PREFIX, $i), $news);
        }

        $manager->flush();
    }

    /**
     * @return string[]
     */
    public function getDependencies(): array
    {
        return  [
            LoadNumberedUsers::class,
            LoadMostActiveUser::class,
        ];
    }
}
