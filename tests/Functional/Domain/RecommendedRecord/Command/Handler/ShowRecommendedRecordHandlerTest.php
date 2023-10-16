<?php

namespace Tests\Functional\Domain\RecommendedRecord\Command\Handler;

use App\Domain\RecommendedRecord\Command\ShowRecommendedRecordCommand;
use App\Domain\RecommendedRecord\Entity\RecommendedRecord;
use Tests\DataFixtures\ORM\Record\Articles\LoadArticlesForSemanticLinks;
use Tests\DataFixtures\ORM\RecommendedRecord\LoadRecommendedRecords;
use Tests\Functional\TestCase;

class ShowRecommendedRecordHandlerTest extends TestCase
{
    public function testHandle(): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadRecommendedRecords::class,
            LoadArticlesForSemanticLinks::class,
        ])->getReferenceRepository();

        $recommendedRecord = $referenceRepository->getReference(LoadRecommendedRecords::REFERENCE_ARTICLE_NAME);
        assert($recommendedRecord instanceof RecommendedRecord);

        $recommendedRecordVisibilityState = $recommendedRecord->isActive();
        $command = new ShowRecommendedRecordCommand($recommendedRecord);

        $this->getCommandBus()->handle($command);

        $this->assertNotEquals($recommendedRecord->isActive(), $recommendedRecordVisibilityState);
    }
}
