<?php

namespace Tests\DataFixtures\ORM\RecommendedRecord;

use App\Domain\RecommendedRecord\Entity\RecommendedRecord;
use App\Domain\Record\Common\Entity\Record;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Ramsey\Uuid\Uuid;
use Tests\DataFixtures\ORM\Record\Articles\LoadArticleForSemanticLinksForRecommendedRecordWithHighPriority;

class LoadRecommendedRecordWithHighPriority extends Fixture implements FixtureInterface, DependentFixtureInterface
{
    public const REFERENCE_NAME = 'recommended-record-with-high-priority';

    public function load(ObjectManager $manager): void
    {
        $id = Uuid::uuid4();
        $record = $this->getReference(LoadArticleForSemanticLinksForRecommendedRecordWithHighPriority::REFERENCE_NAME);
        assert($record instanceof Record);

        $recommendedRecord = new RecommendedRecord($id, $record);
        $recommendedRecord->rewritePriority(100);
        $recommendedRecord->show();

        $this->addReference(self::REFERENCE_NAME, $recommendedRecord);

        $manager->persist($recommendedRecord);
        $manager->flush();
    }

    /**
     * @return string[]
     */
    public function getDependencies(): array
    {
        return [
            LoadArticleForSemanticLinksForRecommendedRecordWithHighPriority::class,
        ];
    }
}
