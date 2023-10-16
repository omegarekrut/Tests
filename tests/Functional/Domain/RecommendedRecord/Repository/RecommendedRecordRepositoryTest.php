<?php

namespace Tests\Functional\Domain\RecommendedRecord\Repository;

use App\Domain\RecommendedRecord\Entity\RecommendedRecord;
use App\Domain\RecommendedRecord\Repository\RecommendedRecordRepository;
use App\Domain\Record\Common\Collection\RecordCollection;
use Tests\DataFixtures\ORM\RecommendedRecord\LoadRecommendedRecordWithHighPriority;
use Tests\DataFixtures\ORM\RecommendedRecord\LoadRecommendedRecordWithLowPriority;
use Tests\DataFixtures\ORM\RecommendedRecord\LoadRecommendedRecordWithMediumPriority;
use Tests\Functional\RepositoryTestCase;

/**
 * @group recommended-record
 */
class RecommendedRecordRepositoryTest extends RepositoryTestCase
{
    private RecommendedRecordRepository $recommendedRecordRepository;
    private RecommendedRecord $firstRatedRecommendedRecord;
    private RecommendedRecord $secondRatedRecommendedRecord;
    private RecommendedRecord $thirdRatedRecommendedRecord;

    protected function setUp(): void
    {
        parent::setUp();

        $this->recommendedRecordRepository = $this->getContainer()->get(RecommendedRecordRepository::class);

        $referenceRepository = $this->loadFixtures([
            LoadRecommendedRecordWithHighPriority::class,
            LoadRecommendedRecordWithMediumPriority::class,
            LoadRecommendedRecordWithLowPriority::class,
        ])->getReferenceRepository();

        $this->firstRatedRecommendedRecord = $referenceRepository->getReference(LoadRecommendedRecordWithHighPriority::REFERENCE_NAME);
        $this->secondRatedRecommendedRecord = $referenceRepository->getReference(LoadRecommendedRecordWithLowPriority::REFERENCE_NAME);
        $this->thirdRatedRecommendedRecord = $referenceRepository->getReference(LoadRecommendedRecordWithMediumPriority::REFERENCE_NAME);
    }

    protected function tearDown(): void
    {
        unset(
            $this->firstRatedRecommendedRecord,
            $this->secondRatedRecommendedRecord,
            $this->thirdRatedRecommendedRecord,
            $this->recommendedRecordRepository
        );

        parent::tearDown();
    }

    public function testSortingRecommendedRecordsByPriority(): void
    {
        $recommendedRecordsLimit = 3;

        $sortingRecommendedRecordsByPriority = $this->recommendedRecordRepository->findRecommendedRecordsExcludingRecords($recommendedRecordsLimit, new RecordCollection());

        $this->assertCount($recommendedRecordsLimit, $sortingRecommendedRecordsByPriority);
        $this->assertEquals($this->firstRatedRecommendedRecord->getRecord(), $sortingRecommendedRecordsByPriority->get(0));
        $this->assertEquals($this->thirdRatedRecommendedRecord->getRecord(), $sortingRecommendedRecordsByPriority->get(1));
        $this->assertEquals($this->secondRatedRecommendedRecord->getRecord(), $sortingRecommendedRecordsByPriority->get(2));
    }
}
