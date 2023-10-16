<?php

namespace Tests\DataFixtures\ORM\Landing;

use App\Domain\Landing\Entity\Landing;
use App\Domain\Landing\Entity\ValueObject\PageContent;
use App\Domain\Landing\Entity\ValueObject\MetaData;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Tests\DataFixtures\ORM\LoadHashtags;

class LoadImportantLandings extends Fixture implements DependentFixtureInterface
{
    private const REFERENCE_PREFIX = 'landing';

    private \Faker\Generator $generator;

    public function __construct(\Faker\Generator $generator)
    {
        $this->generator = $generator;
    }

    public static function getReferenceNameBySlug($slug): string
    {
        return sprintf('%s-%s', self::REFERENCE_PREFIX, $slug);
    }

    private static $importantLandingConfigs = [
        LoadHashtags::HASHTAG_SLUG_WINTER_FISHING => LoadHashtags::HASHTAG_NAME_WINTER_FISHING,
    ];

    public function load(ObjectManager $manager): void
    {
        foreach (self::$importantLandingConfigs as $slug => $heading) {
            $hashTag = $this->getReference(LoadHashtags::getReferenceNameBySlug($slug));
            $description = new PageContent($this->generator->randomHtml(), $this->generator->randomHtml());
            $metadata = new MetaData($this->generator->realText(100), $this->generator->realText());

            $landing = new Landing($hashTag, $heading ,$slug);
            $landing
                ->rewritePageContent($description)
                ->rewriteMetadata($metadata);

            $manager->persist($landing);

            $this->addReference(sprintf('%s-%s', self::REFERENCE_PREFIX, $slug), $landing);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            LoadHashtags::class,
        ];
    }
}
