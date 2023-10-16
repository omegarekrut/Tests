<?php

namespace Tests\DataFixtures\ORM\RecommendedRecord;

use App\Domain\RecommendedRecord\Entity\RecommendedRecord;
use App\Domain\Record\Common\Entity\Record;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Ramsey\Uuid\Uuid;
use Tests\DataFixtures\ORM\Record\Articles\LoadArticlesForSemanticLinks;
use Tests\DataFixtures\ORM\Record\Video\LoadVideoWithHashtagInText;
use Tests\DataFixtures\ORM\User\LoadMostActiveUser;

class LoadRecommendedRecords extends Fixture implements FixtureInterface, DependentFixtureInterface
{
    public const REFERENCE_ARTICLE_NAME = 'recommended-article-record';
    public const REFERENCE_VIDEO_NAME = 'recommended-video-record';

    public function load(ObjectManager $manager): void
    {
        foreach ($this->getRecords() as $referenceName => $record) {
            $id = Uuid::uuid4();
            assert($record instanceof Record);

            $recommendedRecord = new RecommendedRecord($id, $record);

            $this->addReference($referenceName, $recommendedRecord);

            $manager->persist($recommendedRecord);

            if (self::REFERENCE_VIDEO_NAME !== $referenceName) {
                continue;
            }

            $recommendedRecord->show();
        }

        $manager->flush();
    }

    /**
     * @return string[]
     */
    private function getRecords(): array
    {
        return [
            self::REFERENCE_ARTICLE_NAME => $this->getReference(LoadArticlesForSemanticLinks::REFERENCE_NAME),
            self::REFERENCE_VIDEO_NAME => $this->getReference(LoadVideoWithHashtagInText::REFERENCE_NAME),
        ];
    }

    /**
     * @return string[]
     */
    public function getDependencies(): array
    {
        return [
            LoadArticlesForSemanticLinks::class,
            LoadVideoWithHashtagInText::class,
            LoadMostActiveUser::class,
        ];
    }
}
