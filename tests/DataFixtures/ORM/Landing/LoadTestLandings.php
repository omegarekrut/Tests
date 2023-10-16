<?php

namespace Tests\DataFixtures\ORM\Landing;

use App\Domain\Landing\Entity\Landing;
use App\Domain\Landing\Entity\ValueObject\MetaData;
use App\Domain\Landing\Entity\ValueObject\PageContent;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Tests\DataFixtures\ORM\LoadHashtags;

class LoadTestLandings extends Fixture implements DependentFixtureInterface
{
    public const REFERENCE_NAME = 'landing-test';

    private \Faker\Generator $generator;

    public function __construct(\Faker\Generator $generator)
    {
        $this->generator = $generator;
    }

    public function load(ObjectManager $manager): void
    {
        $hashTag = $this->getReference(LoadHashtags::getReferenceNameBySlug(LoadHashtags::HASHTAG_SLUG_SUMMER_FISHING));

        $pageContent = new PageContent($this->generator->randomHtml(), $this->generator->randomHtml());
        $metaData = new MetaData($this->generator->realText(100), $this->generator->realText());

        $landing = new Landing($hashTag, 'landing-heading' ,'landing-slug');
        $landing
            ->rewritePageContent($pageContent)
            ->rewriteMetaData($metaData);

        $manager->persist($landing);

        $this->addReference(self::REFERENCE_NAME, $landing);

        $manager->flush();
    }

    /**
     * @inheritdoc
     */
    public function getDependencies(): array
    {
        return [
            LoadHashtags::class,
        ];
    }
}
