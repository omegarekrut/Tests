<?php

namespace Tests\DataFixtures\ORM\Record\Tidings;

use App\Domain\Record\Tidings\Entity\Tidings;
use Doctrine\Common\Persistence\ObjectManager;
use Faker\Generator;
use Tests\DataFixtures\Helper\AuthorHelper;
use Tests\DataFixtures\Helper\CommentHelper;
use Tests\DataFixtures\Helper\MediaHelper;

class LoadTidingsWithVideos extends LoadNumberedTidings
{
    protected const REFERENCE_PREFIX = 'tidings-with-videos';
    protected const COUNT = 10;

    private Generator $generator;
    private CommentHelper $commentHelper;
    private MediaHelper $mediaHelper;
    private AuthorHelper $authorHelper;

    public function __construct(\Faker\Generator $generator, CommentHelper $commentHelper, MediaHelper $mediaHelper, AuthorHelper $authorHelper)
    {
        $this->generator = $generator;
        $this->commentHelper = $commentHelper;
        $this->mediaHelper = $mediaHelper;
        $this->authorHelper = $authorHelper;
    }

    public function load(ObjectManager $manager): void
    {
        for ($i = 1; $i <= static::COUNT; $i++) {
            /** @var Tidings $tidings */
            $tidings = $this->getTidings($manager, $this->generator, $this->authorHelper, $this->mediaHelper, $this->commentHelper);

            $videoUrl = $this->generator->videoUrl();
            $tidings->addVideoUrl($videoUrl);

            $manager->persist($tidings);

            $this->addReference(sprintf('%s-%d', static::REFERENCE_PREFIX, $i), $tidings);
        }

        $manager->flush();
    }
}
