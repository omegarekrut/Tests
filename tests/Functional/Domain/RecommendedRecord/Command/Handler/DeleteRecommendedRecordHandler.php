<?php

namespace Tests\Functional\Domain\RecommendedRecord\Command\Handler;

use App\Domain\RecommendedRecord\Command\DeleteRecommendedRecordCommand;
use App\Domain\RecommendedRecord\Entity\RecommendedRecord;
use Tests\DataFixtures\ORM\Record\Articles\LoadArticlesForSemanticLinks;
use Tests\DataFixtures\ORM\RecommendedRecord\LoadRecommendedRecords;
use Tests\Functional\TestCase;

class DeleteRecommendedRecordHandler extends TestCase
{
    public function testHandle(): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadRecommendedRecords::class,
            LoadArticlesForSemanticLinks::class,
        ])->getReferenceRepository();

        $recommendedRecord = $referenceRepository->getReference(LoadRecommendedRecords::REFERENCE_ARTICLE_NAME);
        assert($recommendedRecord instanceof RecommendedRecord);

        $recommendedRecordId = $recommendedRecord->getId();
        $recommendedRecordRepository = $this->getEntityManager()->getRepository(RecommendedRecord::class);

        $command = new DeleteRecommendedRecordCommand($recommendedRecord);
        $this->getCommandBus()->handle($command);

        $this->assertNull($recommendedRecordRepository->find($recommendedRecordId));
    }
}
