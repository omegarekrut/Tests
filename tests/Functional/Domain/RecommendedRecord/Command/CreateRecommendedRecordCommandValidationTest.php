<?php

namespace Tests\Functional\Domain\RecommendedRecord\Command;

use App\Domain\RecommendedRecord\Command\CreateRecommendedRecordCommand;
use Ramsey\Uuid\Uuid;
use Tests\DataFixtures\ORM\RecommendedRecord\LoadRecommendedRecords;
use Tests\Functional\ValidationTestCase;

class CreateRecommendedRecordCommandValidationTest extends ValidationTestCase
{
    public function testValidatorShouldThrowRecommendedRecordPropertyError(): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadRecommendedRecords::class,
        ])->getReferenceRepository();

        $recommendedRecord = $referenceRepository->getReference(LoadRecommendedRecords::REFERENCE_ARTICLE_NAME);

        $id = Uuid::uuid4();
        $record = $recommendedRecord->getRecord();
        $command = new CreateRecommendedRecordCommand($id, $record);

        $this->getValidator()->validate($command);
        $this->assertFieldInvalid('record', sprintf('Рекомендуемое из записи с заголовком %s уже существует.', $record->getTitle()));
    }
}
