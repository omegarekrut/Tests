<?php

namespace Tests\Functional\Domain\RecommendedRecord\Command\Handler;

use App\Domain\RecommendedRecord\Command\HideRecommendedRecordByRecordIdCommand;
use App\Domain\RecommendedRecord\Command\ShowRecommendedRecordCommand;
use App\Domain\RecommendedRecord\Entity\RecommendedRecord;
use Tests\DataFixtures\ORM\RecommendedRecord\LoadRecommendedRecords;
use Tests\DataFixtures\ORM\Record\Articles\LoadArticlesForSemanticLinks;
use Tests\Functional\TestCase;

class HideRecommendedRecordByRecordIdHandlerTest extends TestCase
{
    public function testHandle(): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadRecommendedRecords::class,
            LoadArticlesForSemanticLinks::class,
        ])->getReferenceRepository();

        $recommendedRecord = $referenceRepository->getReference(LoadRecommendedRecords::REFERENCE_ARTICLE_NAME);
        assert($recommendedRecord instanceof RecommendedRecord);

        $recordId = $recommendedRecord->getRecord()->getId();

        $showRecommendedRecordCommand = new ShowRecommendedRecordCommand($recommendedRecord);
        $this->getCommandBus()->handle($showRecommendedRecordCommand);

        $recommendedRecordVisibilityState = $recommendedRecord->isActive();

        $command = new HideRecommendedRecordByRecordIdCommand($recordId);
        $this->getCommandBus()->handle($command);

        $this->assertNotEquals($recommendedRecord->isActive(), $recommendedRecordVisibilityState);
    }
}
