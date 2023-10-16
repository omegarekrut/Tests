<?php

namespace Tests\Functional\Domain\RecommendedRecord\Command\Handler;

use App\Domain\RecommendedRecord\Command\UpdateRecommendedRecordCommand;
use App\Domain\RecommendedRecord\Entity\RecommendedRecord;
use Tests\DataFixtures\ORM\RecommendedRecord\LoadRecommendedRecords;
use Tests\Functional\TestCase;

class UpdateRecommendedRecordHandlerTest extends TestCase
{
    public function testHandle(): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadRecommendedRecords::class,
        ])->getReferenceRepository();

        $recommendedRecord = $referenceRepository->getReference(LoadRecommendedRecords::REFERENCE_ARTICLE_NAME);
        assert($recommendedRecord instanceof RecommendedRecord);

        $command = new UpdateRecommendedRecordCommand($recommendedRecord);
        $command->priority = 10;

        $this->getCommandBus()->handle($command);

        $this->assertEquals($command->priority, $recommendedRecord->getPriority());
    }
}
