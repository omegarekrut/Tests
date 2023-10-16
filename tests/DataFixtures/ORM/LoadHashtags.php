<?php

namespace Tests\DataFixtures\ORM;

use App\Domain\Hashtag\Entity\Hashtag;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class LoadHashtags extends Fixture
{
    public const HASHTAG_SLUG_WINTER_FISHING = 'zimnjaja-rybalka';
    public const HASHTAG_NAME_WINTER_FISHING = 'зимняяРыбалка';
    public const HASHTAG_SLUG_SUMMER_FISHING = 'letnjaja-rybalka';
    public const HASHTAG_NAME_SUMMER_FISHING = 'летняяРыбалка';
    public const HASHTAG_SLUG_FISHING = 'rybalka';
    public const HASHTAG_NAME_FISHING = 'рыбалка';

    private const REFERENCE_PREFIX = 'hashtag';

    private static $importantLandingConfigs = [
        self::HASHTAG_SLUG_WINTER_FISHING => self::HASHTAG_NAME_WINTER_FISHING,
        self::HASHTAG_SLUG_SUMMER_FISHING => self::HASHTAG_NAME_SUMMER_FISHING,
        self::HASHTAG_SLUG_FISHING  => self::HASHTAG_NAME_FISHING,
    ];

    public static function getReferenceNameBySlug($slug): string
    {
        return sprintf('%s-%s', self::REFERENCE_PREFIX, $slug);
    }

    public function load(ObjectManager $manager): void
    {
        foreach (self::$importantLandingConfigs as $slug => $name) {
            $hashTag = new Hashtag($name);

            $manager->persist($hashTag);

            $this->addReference(sprintf('%s-%s', self::REFERENCE_PREFIX, $slug), $hashTag);
        }

        $manager->flush();
    }
}
