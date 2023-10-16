<?php

namespace Tests\Functional\Domain\Statistic\Repository;

use App\Domain\Statistic\Repository\UsersActivityStatisticQuery;
use App\Domain\Statistic\Repository\UsersActivityStatisticRepository;
use Carbon\Carbon;
use Tests\DataFixtures\ORM\Comment\LoadOneComment;
use Tests\DataFixtures\ORM\Record\LoadMaps;
use Tests\DataFixtures\ORM\Record\LoadNews;
use Tests\DataFixtures\ORM\Record\Tidings\LoadTidingsWithHashtag;
use Tests\DataFixtures\ORM\Record\LoadArticles;
use Tests\DataFixtures\ORM\Record\LoadGallery;
use Tests\DataFixtures\ORM\Record\LoadVideos;
use Tests\DataFixtures\ORM\User\LoadMostActiveUser;
use Tests\Functional\TestCase;

/**
 * @group statistic
 */
class UsersActivityStatisticRepositoryTest extends TestCase
{
    /** @var UsersActivityStatisticQuery */
    private $statisticQuery;

    /** @var UsersActivityStatisticRepository */
    private $statisticRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->statisticQuery = new UsersActivityStatisticQuery();
        $this->statisticRepository = $this->getContainer()->get(UsersActivityStatisticRepository::class);
    }

    protected function tearDown(): void
    {
        unset(
            $this->statisticRepository,
            $this->statisticQuery
        );

        parent::tearDown();
    }

    public function testNoNewUsersReport(): void
    {
        $this->statisticQuery->periodTo = Carbon::yesterday();
        $report = $this->statisticRepository->getNewUsersReport($this->statisticQuery);
        $reportUnits = $report->getUnits();

        $this->assertCount(0, $reportUnits);
    }

    public function testGetNewUsersReport(): void
    {
        $this->loadFixtures([LoadMostActiveUser::class]);

        $report = $this->statisticRepository->getNewUsersReport($this->statisticQuery);
        $reportUnits = $report->getUnits();

        $this->assertCount(1, $reportUnits);
        $this->assertEquals(1, $reportUnits[0]->getValue());
    }

    public function testNoNewCommentsReport(): void
    {
        $this->statisticQuery->periodTo = Carbon::yesterday();
        $report = $this->statisticRepository->getNewUsersReport($this->statisticQuery);
        $reportUnits = $report->getUnits();

        $this->assertCount(0, $reportUnits);
    }

    public function testGetNewCommentsReport(): void
    {
        $this->loadFixtures([LoadOneComment::class]);

        $report = $this->statisticRepository->getNewCommentsReport($this->statisticQuery);
        $reportUnits = $report->getUnits();

        $this->assertCount(1, $reportUnits);
        $this->assertEquals(1, $reportUnits[0]->getValue());
    }

    public function testNoNewRecordsReport(): void
    {
        $this->statisticQuery->periodTo = Carbon::yesterday();
        $report = $this->statisticRepository->getNewRecordsReport($this->statisticQuery);
        $reportUnits = $report->getUnits();

        $this->assertCount(0, $reportUnits);
    }

    public function testGetNewRecordsReport(): void
    {
        $this->loadFixtures([LoadTidingsWithHashtag::class, LoadNews::class]);

        $report = $this->statisticRepository->getNewRecordsReport($this->statisticQuery);
        $reportUnits = $report->getUnits();

        $totalRecordsCount = LoadNews::COUNT + LoadTidingsWithHashtag::COUNT;

        $this->assertCount(1, $reportUnits);
        $this->assertEquals($totalRecordsCount, $reportUnits[0]->getValue());
    }

    public function testNoNewTidingsReport(): void
    {
        $this->statisticQuery->periodTo = Carbon::yesterday();
        $report = $this->statisticRepository->getNewTidingsReport($this->statisticQuery);
        $reportUnits = $report->getUnits();

        $this->assertCount(0, $reportUnits);
    }

    public function testGetNewTidingsReport(): void
    {
        $this->loadFixtures([LoadTidingsWithHashtag::class]);

        $report = $this->statisticRepository->getNewTidingsReport($this->statisticQuery);
        $reportUnits = $report->getUnits();

        $this->assertCount(1, $reportUnits);
        $this->assertEquals(LoadTidingsWithHashtag::COUNT, $reportUnits[0]->getValue());
    }

    public function testNoNewArticlesReport(): void
    {
        $this->statisticQuery->periodTo = Carbon::yesterday();
        $report = $this->statisticRepository->getNewUsersReport($this->statisticQuery);
        $reportUnits = $report->getUnits();

        $this->assertCount(0, $reportUnits);
    }

    public function testGetNewArticlesReport(): void
    {
        $this->loadFixtures([LoadArticles::class]);

        $report = $this->statisticRepository->getNewArticlesReport($this->statisticQuery);
        $reportUnits = $report->getUnits();

        $this->assertCount(1, $reportUnits);
        $this->assertEquals(LoadArticles::FULL_COUNT, $reportUnits[0]->getValue());
    }


    public function testNoNewGalleryReport(): void
    {
        $this->statisticQuery->periodTo = Carbon::yesterday();
        $report = $this->statisticRepository->getNewGalleryReport($this->statisticQuery);
        $reportUnits = $report->getUnits();

        $this->assertCount(0, $reportUnits);
    }

    public function testGetNewGalleryReport(): void
    {
        $this->loadFixtures([LoadGallery::class]);

        $report = $this->statisticRepository->getNewGalleryReport($this->statisticQuery);
        $reportUnits = $report->getUnits();

        $this->assertCount(1, $reportUnits);
        $this->assertEquals(LoadGallery::COUNT, $reportUnits[0]->getValue());
    }

    public function testNoNewVideosReport(): void
    {
        $this->statisticQuery->periodTo = Carbon::yesterday();
        $report = $this->statisticRepository->getNewUsersReport($this->statisticQuery);
        $reportUnits = $report->getUnits();

        $this->assertCount(0, $reportUnits);
    }

    public function testGetNewVideosReport(): void
    {
        $this->loadFixtures([LoadVideos::class]);

        $report = $this->statisticRepository->getNewVideosReport($this->statisticQuery);
        $reportUnits = $report->getUnits();

        $this->assertCount(1, $reportUnits);
        $this->assertEquals(LoadVideos::COUNT, $reportUnits[0]->getValue());
    }

    public function testNoNewMapsReport(): void
    {
        $this->statisticQuery->periodTo = Carbon::yesterday();
        $report = $this->statisticRepository->getNewMapsReport($this->statisticQuery);
        $reportUnits = $report->getUnits();

        $this->assertCount(0, $reportUnits);
    }

    public function testGetNewMapsReport(): void
    {
        $this->loadFixtures([LoadMaps::class]);

        $report = $this->statisticRepository->getNewMapsReport($this->statisticQuery);
        $reportUnits = $report->getUnits();

        $this->assertCount(1, $reportUnits);
        $this->assertEquals(LoadMaps::COUNT, $reportUnits[0]->getValue());
    }
}
