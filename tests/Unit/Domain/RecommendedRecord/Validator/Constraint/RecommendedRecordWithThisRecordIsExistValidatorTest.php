<?php

namespace Tests\Unit\Domain\RecommendedRecord\Validator\Constraint;

use App\Domain\RecommendedRecord\Entity\RecommendedRecord;
use App\Domain\RecommendedRecord\Repository\RecommendedRecordRepository;
use App\Domain\RecommendedRecord\Validator\Constraint\RecommendedRecordWithThisRecordIsExist;
use App\Domain\RecommendedRecord\Validator\Constraint\RecommendedRecordWithThisRecordIsExistValidator;
use App\Domain\Record\Common\Entity\Record;
use Tests\Unit\Mock\ValidatorExecutionContextMock;
use Tests\Unit\TestCase;

class RecommendedRecordWithThisRecordIsExistValidatorTest extends TestCase
{
    private ValidatorExecutionContextMock $executionContext;
    private RecommendedRecordWithThisRecordIsExist $constraint;

    protected function setUp(): void
    {
        parent::setUp();

        $this->executionContext = new ValidatorExecutionContextMock();

        $this->constraint = new RecommendedRecordWithThisRecordIsExist();
    }

    public function testValidationForNonExistentRecommendedRecordShouldPass(): void
    {
        $record = $this->createRecordMock();
        $validator = $this->createValidator($this->createRecommendedRepositoryMockForValidTest());
        $validator->validate($record, $this->constraint);

        $this->assertFalse($this->executionContext->hasViolations());
    }

    public function testValidationForExistentRecommendedRecordShouldNotPass(): void
    {
        $record = $this->createRecordMock();
        $validator = $this->createValidator($this->createRecommendedRepositoryMockForInValidTest($this->createRecommendedRecordMock()));
        $validator->validate($record, $this->constraint);

        $this->assertTrue($this->executionContext->hasViolations());
    }

    private function createRecommendedRepositoryMockForValidTest(): RecommendedRecordRepository
    {
        $stub = $this->createMock(RecommendedRecordRepository::class);
        $stub
            ->method('findByRecordId')
            ->willReturn(null);

        return $stub;
    }

    private function createRecommendedRepositoryMockForInValidTest(RecommendedRecord $recommendedRecord): RecommendedRecordRepository
    {
        $stub = $this->createMock(RecommendedRecordRepository::class);
        $stub
            ->method('findByRecordId')
            ->willReturn($recommendedRecord);

        return $stub;
    }

    private function createRecordMock(): Record
    {
        return $this->createMock(Record::class);
    }

    private function createRecommendedRecordMock(): RecommendedRecord
    {
        return $this->createMock(RecommendedRecord::class);
    }

    private function createValidator(RecommendedRecordRepository $recommendedRecordRepository): RecommendedRecordWithThisRecordIsExistValidator
    {
        $validator = new RecommendedRecordWithThisRecordIsExistValidator($recommendedRecordRepository);
        $validator->initialize($this->executionContext);

        return $validator;
    }
}
