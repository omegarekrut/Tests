<?php

namespace Tests\Functional\Domain\Record\Common\Repository;

use App\Domain\Category\Entity\Category;
use App\Domain\Company\Entity\Company;
use App\Domain\Rating\ValueObject\RatingInfo;
use App\Domain\Record\Common\Collection\RecordCollection;
use App\Domain\Record\Common\Entity\Record;
use App\Domain\Record\Common\Repository\RecordRepository;
use App\Domain\Record\Tackle\Entity\TackleReview;
use App\Domain\Record\Tidings\Entity\Tidings;
use App\Domain\Record\Video\Entity\Video;
use Carbon\Carbon;
use DatePeriod;
use Tests\DataFixtures\ORM\Company\Company\LoadCompanyWithOwner;
use Tests\DataFixtures\ORM\Record\LoadArticles;
use Tests\DataFixtures\ORM\Record\LoadGallery;
use Tests\DataFixtures\ORM\Record\LoadNews;
use Tests\DataFixtures\ORM\Record\LoadRecordsWithRatingInCategory;
use Tests\DataFixtures\ORM\Record\LoadTackleReviews;
use Tests\DataFixtures\ORM\Record\LoadVideos;
use Tests\DataFixtures\ORM\Record\Tidings\LoadNumberedTidings;
use Tests\DataFixtures\ORM\Record\Tidings\LoadSimpleTidings;
use Tests\DataFixtures\ORM\Record\Tidings\LoadTidingsWithRegion;
use Tests\DataFixtures\ORM\Record\Video\LoadInterestingVideoFromRegion;
use Tests\DataFixtures\ORM\Record\Video\LoadSimpleVideo;
use Tests\DataFixtures\ORM\Record\Video\LoadVideoFromRegion;
use Tests\Functional\RepositoryTestCase;

/**
 * @group record
 */
class RecordRepositoryTest extends RepositoryTestCase
{
    private const FIRST_RATING = 1000;
    private const SECOND_RATING = 900;
    private const THIRD_RATING = 800;

    private RecordRepository $recordRepository;

    private Record $firstRatedRecord;

    private Record $secondRatedRecord;

    private Record $thirdRatedRecord;

    protected function setUp(): void
    {
        parent::setUp();

        $this->recordRepository = $this->getContainer()->get(RecordRepository::class);
    }

    protected function tearDown(): void
    {
        unset(
            $this->firstRatedRecord,
            $this->secondRatedRecord,
            $this->thirdRatedRecord,
            $this->recordRepository
        );

        parent::tearDown();
    }

    public function testFindMostRatedForPeriodWithoutRecordTypes(): void
    {
        $this->setManyRecords();
        $recordsLimit = 10;
        $periodEnd = Carbon::today()->endOfDay();
        $periodStart = $periodEnd->copy()->subWeek()->startOfDay();
        $period = new DatePeriod($periodStart, $periodEnd->diff($periodStart), $periodEnd);
        $excludedTypes = [TackleReview::class];

        $mostRatedRecords = $this->recordRepository->findMostRatedForPeriodWithoutRecordTypes($period, $excludedTypes, $recordsLimit);

        $this->assertCount($recordsLimit, $mostRatedRecords);
        $this->assertEquals($this->secondRatedRecord, $mostRatedRecords->get(0));
        $this->assertEquals($this->thirdRatedRecord, $mostRatedRecords->get(1));
    }

    public function testGetCountWithPositiveRatingByCategory(): void
    {
        $this->setManyRecords();
        $referenceRepository = $this->loadFixtures([
            LoadRecordsWithRatingInCategory::class,
        ])->getReferenceRepository();
        $expectedCount = LoadRecordsWithRatingInCategory::POSITIVE_RECORDS_COUNT;

        $category = $referenceRepository->getReference(LoadRecordsWithRatingInCategory::getReferenceCategory());
        assert($category instanceof Category);

        $actualCount = $this->recordRepository->getCountWithPositiveRatingByCategory($category);

        $this->assertEquals($expectedCount, $actualCount);
    }

    public function testGetRecordMatchingRegion(): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadSimpleTidings::class,
            LoadTidingsWithRegion::class,
            LoadCompanyWithOwner::class,
            LoadInterestingVideoFromRegion::class,
            LoadSimpleVideo::class,
            LoadVideoFromRegion::class,
        ])->getReferenceRepository();

        $recordWithRegion = $referenceRepository->getReference(LoadTidingsWithRegion::REFERENCE_NAME);
        assert($recordWithRegion instanceof Tidings);

        $simpleTidings = $referenceRepository->getReference(LoadSimpleTidings::REFERENCE_NAME);
        assert($simpleTidings instanceof Tidings);

        $company = $referenceRepository->getReference(LoadCompanyWithOwner::REFERENCE_NAME);
        assert($company instanceof Company);

        $interestingVideoRecordFromRegion = $referenceRepository->getReference(LoadInterestingVideoFromRegion::REFERENCE_NAME);
        assert($interestingVideoRecordFromRegion instanceof Video);

        $videoRecordFromRegion = $referenceRepository->getReference(LoadVideoFromRegion::REFERENCE_NAME);
        assert($videoRecordFromRegion instanceof Video);

        $simpleVideo = $referenceRepository->getReference(LoadSimpleVideo::REFERENCE_NAME);
        assert($simpleVideo instanceof Video);

        $actualRecords = $this->recordRepository->getInterestedPageExcludingTacklesWithCompaniesArticlesSubscription(1, 20, [$company], $recordWithRegion->getRegion());
        assert($actualRecords instanceof RecordCollection);

        $this->assertTrue($actualRecords->contains($recordWithRegion));
        $this->assertTrue($actualRecords->contains($simpleTidings));
        $this->assertTrue($actualRecords->contains($interestingVideoRecordFromRegion));
        $this->assertFalse($actualRecords->contains($videoRecordFromRegion));
        $this->assertFalse($actualRecords->contains($simpleVideo));
    }

    private function setRatingInfoForRecord(int $rating, Record $record): void
    {
        $ratingInfo = new RatingInfo($rating, $rating, 0, $rating);

        $record->updateRatingInfo($ratingInfo);
    }

    private function setManyRecords(): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadNews::class,
            LoadTackleReviews::class,
            LoadArticles::class,
            LoadNumberedTidings::class,
            LoadGallery::class,
            LoadVideos::class,
        ])->getReferenceRepository();
        $objectManager = $referenceRepository->getManager();

        $firstRatedRecord = $referenceRepository->getReference(LoadTackleReviews::getRandReferenceName());
        assert($firstRatedRecord instanceof Record);

        $secondRatedRecord = $referenceRepository->getReference(LoadArticles::getRandReferenceName());
        assert($secondRatedRecord instanceof Record);

        $thirdRatedRecord = $referenceRepository->getReference(LoadNumberedTidings::getRandReferenceName());
        assert($thirdRatedRecord instanceof Record);

        $this->firstRatedRecord = $firstRatedRecord;
        $this->setRatingInfoForRecord(self::FIRST_RATING, $this->firstRatedRecord);

        $this->secondRatedRecord = $secondRatedRecord;
        $this->setRatingInfoForRecord(self::SECOND_RATING, $this->secondRatedRecord);

        $this->thirdRatedRecord = $thirdRatedRecord;
        $this->setRatingInfoForRecord(self::THIRD_RATING, $this->thirdRatedRecord);

        $objectManager->flush();
    }
}
