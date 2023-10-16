<?php

namespace Tests\Functional\Domain\RecommendedRecord\Command\Handler;

use App\Domain\RecommendedRecord\Command\HideRecommendedRecordCommand;
use App\Domain\RecommendedRecord\Command\ShowRecommendedRecordCommand;
use App\Domain\RecommendedRecord\Entity\RecommendedRecord;
use Tests\DataFixtures\ORM\Record\Articles\LoadArticlesForSemanticLinks;
use Tests\DataFixtures\ORM\RecommendedRecord\LoadRecommendedRecords;
use Tests\Functional\TestCase;

class HideRecommendedRecordHandlerTest extends TestCase
{
    public function testHandle(): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadRecommendedRecords::class,
            LoadArticlesForSemanticLinks::class,
        ])->getReferenceRepository();

        $recommendedRecord = $referenceRepository->getReference(LoadRecommendedRecords::REFERENCE_ARTICLE_NAME);
        assert($recommendedRecord instanceof RecommendedRecord);

        $showRecommendedRecordCommand = new ShowRecommendedRecordCommand($recommendedRecord);
        $this->getCommandBus()->handle($showRecommendedRecordCommand);

        $recommendedRecordVisibilityState = $recommendedRecord->isActive();

        $hideRecommendedRecordCommand = new HideRecommendedRecordCommand($recommendedRecord);
        $this->getCommandBus()->handle($hideRecommendedRecordCommand);

        $this->assertNotEquals($recommendedRecord->isActive(), $recommendedRecordVisibilityState);
    }
}
