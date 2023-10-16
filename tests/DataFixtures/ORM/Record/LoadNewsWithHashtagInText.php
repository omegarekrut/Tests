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

class LoadNewsWithHashtagInText extends Fixture implements DependentFixtureInterface
{
    public const REFERENCE_NAME = 'news-with-hashtag-in-text';

    private \Faker\Generator $generator;
    private AuthorHelper $authorHelper;
    private MediaHelper $mediaHelper;

    public function __construct(\Faker\Generator $generator, AuthorHelper $authorHelper, MediaHelper $mediaHelper)
    {
        $this->generator = $generator;
        $this->authorHelper = $authorHelper;
        $this->mediaHelper = $mediaHelper;
    }

    public function load(ObjectManager $manager): void
    {
        $text = 'text for news with #hashtag in text';

        $news = new News(
            $this->generator->realText(20),
            $this->generator->realText(),
            $this->authorHelper->chooseAuthor($this),
            $text,
            $this->mediaHelper->createImage()
        );

        $manager->persist($news);
        $this->addReference(self::REFERENCE_NAME, $news);

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            LoadNumberedUsers::class,
            LoadMostActiveUser::class,
        ];
    }
}
