<?php

namespace Tests\DataFixtures\ORM\Record\Tidings;

use App\Domain\Hashtag\Entity\Hashtag;
use App\Domain\Record\Tidings\Entity\Tidings;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Faker\Generator;
use Tests\DataFixtures\ORM\LoadHashtags;
use Tests\DataFixtures\ORM\User\LoadMostActiveUser;
use Tests\DataFixtures\ORM\User\LoadNumberedUsers;

class LoadTidingsWithHashtag extends LoadNumberedTidings implements DependentFixtureInterface
{
    protected const REFERENCE_PREFIX = 'tidings-with-hashtag';
    public const COUNT = 10;

    public function load(ObjectManager $manager): void
    {
        parent::load($manager);

        /** @var Hashtag $hashtag */
        $hashtag = $this->getReference(LoadHashtags::getReferenceNameBySlug(LoadHashtags::HASHTAG_SLUG_WINTER_FISHING));

        for ($i = 1; $i <= static::COUNT; $i++) {
            /** @var Tidings $tidings */
            $tidings = $this->getReference(self::getRandReferenceName());

            if (!$tidings->isAttachedHashtag($hashtag)) {
                $tidings->addHashtag($hashtag);
            }
        }

        $manager->flush();
    }

    /**
     * @return string[]
     */
    public function getDependencies(): array
    {
        return array_merge(parent::getDependencies(), [
            LoadHashtags::class,
        ]);
    }

    protected function getText(Generator $faker): string
    {
        $text = $faker->realText();

        return substr_replace($text, ' #'.LoadHashtags::HASHTAG_NAME_WINTER_FISHING.' ', random_int(0, strlen($text)), 0);
    }
}
