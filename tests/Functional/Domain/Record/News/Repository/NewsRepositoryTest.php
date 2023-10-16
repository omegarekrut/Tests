<?php

namespace Tests\Functional\Domain\Record\News\Repository;

use App\Domain\Record\News\Repository\NewsRepository;
use Carbon\Carbon;
use DatePeriod;
use Tests\DataFixtures\ORM\Record\LoadTodayNews;
use Tests\DataFixtures\ORM\Record\News\LoadNewsForPublishTomorrow;
use Tests\DataFixtures\ORM\Record\News\LoadNotActualNews;
use Tests\Functional\RepositoryTestCase;

/**
 * @group record
 */
class NewsRepositoryTest extends RepositoryTestCase
{
    private NewsRepository $newsRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->newsRepository = $this->getContainer()->get(NewsRepository::class);
    }

    public function testFindAllForPeriod(): void
    {
        $this->loadFixtures([
            LoadTodayNews::class,
        ]);

        $todayStart = Carbon::today()->startOfDay();
        $todayEnd = Carbon::today()->endOfDay();
        $today = new DatePeriod($todayStart, $todayEnd->diff($todayStart), $todayEnd);

        $yesterdayStart = Carbon::yesterday()->startOfDay();
        $yesterdayEnd = Carbon::yesterday()->endOfDay();
        $yesterday = new DatePeriod($yesterdayStart, $yesterdayEnd->diff($yesterdayStart), $yesterdayEnd);

        $newsForToday = $this->newsRepository->findAllForPeriod($today);
        $newsForYesterday = $this->newsRepository->findAllForPeriod($yesterday);

        $this->assertCount(1, $newsForToday);
        $this->assertCount(0, $newsForYesterday);
    }

    public function testFindOnlyFreshNewsForIndexPage(): void
    {
        $references = $this->loadFixtures([
            LoadNotActualNews::class,
            LoadNewsForPublishTomorrow::class,
            LoadTodayNews::class,
        ])->getReferenceRepository();

        $todayNews = $references->getReference(LoadTodayNews::REFERENCE_NAME);

        $news = $this->newsRepository->findFreshForIndexPage();

        $this->assertCount(1, $news);
        $this->assertContains($todayNews, $news);
    }

    public function testCannotFindNewsWhichHasPublishAtTomorrow(): void
    {
        $this->loadFixtures([
            LoadNewsForPublishTomorrow::class,
        ]);

        $news = $this->newsRepository->findAll();

        $this->assertEmpty($news);
    }
}
