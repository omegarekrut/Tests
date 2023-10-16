<?php

namespace Tests\Unit\Domain\User\Rating;

use App\Bridge\Xenforo\ForumApiInterface;
use App\Bridge\Xenforo\Provider\StatisticsProviderInterface;
use App\Domain\Statistic\Entity\AggregateUserPositiveRatingReport;
use App\Domain\Statistic\Entity\UserRecordsPositiveRatingReport;
use App\Domain\Statistic\Repository\UserRecordsPositiveRatingReportRepository;
use App\Domain\User\Entity\User;
use App\Domain\User\Rating\UserRatingCalculator;
use DateInterval;
use DatePeriod;
use DateTimeImmutable;
use DateTimeInterface;
use Generator;
use Tests\Unit\TestCase;

class UserRatingCalculatorTest extends TestCase
{
    private ForumApiInterface $forumApi;

    protected function setUp(): void
    {
        parent::setUp();

        $this->forumApi = $this->getForumApi(0);
    }

    /**
     * @dataProvider getUserIdentifiersWithDisabledRating
     */
    public function testCalculateAllPeriodUserRatingForUserWithoutRating(int $userId): void
    {
        $user = $this->getUser($userId);

        $userRatingCalculator = new UserRatingCalculator(
            $this->createMock(ForumApiInterface::class),
            $this->createMock(UserRecordsPositiveRatingReportRepository::class)
        );

        $this->assertEquals(0, $userRatingCalculator->calculateUserRatingForAllPeriod($user));
    }

    /**
     * @dataProvider getUserIdentifiersWithDisabledRating
     */
    public function testCalculateRatingForUserWithoutRating(int $userId): void
    {
        $user = $this->getUser($userId);

        $userRatingCalculator = new UserRatingCalculator(
            $this->createMock(ForumApiInterface::class),
            $this->createMock(UserRecordsPositiveRatingReportRepository::class)
        );

        $this->assertEquals(0, $userRatingCalculator->calculateUserRatingForPeriod($user, self::getDatePeriod()));
    }

    public function getUserIdentifiersWithDisabledRating(): Generator
    {
        yield [10627];

        yield [63269];

        yield [87677];
    }

    public function testCalculateAllUserRatingForUserWithoutLikedMaterials(): void
    {
        $ratingReportRepository = $this->getUserRecordsPositiveRatingReportRepository(0, 0, 0, 0);

        $this->assertExpectedUserRatingForAllPeriod(0, $ratingReportRepository);
    }

    public function testCalculateRatingForUserWithoutLikedMaterials(): void
    {
        $ratingReportRepository = $this->getUserRecordsPositiveRatingReportRepository(0, 0, 0, 0);

        $this->assertExpectedUserRatingForPeriod(0, $ratingReportRepository);
    }

    /**
     * @dataProvider getUserRatingDataForVideo
     */
    public function testCalculateAllUserRatingForUserOnlyWithVideoRecord(int $expectedRating, int $positivRecordRating): void
    {
        $ratingReportRepository = $this->getUserRecordsPositiveRatingReportRepository($positivRecordRating, 0, 0, 0);

        $this->assertExpectedUserRatingForAllPeriod($expectedRating, $ratingReportRepository);
    }

    /**
     * @dataProvider getUserRatingDataForVideo
     */
    public function testCalculateRatingForUserOnlyWithVideoRecord(int $expectedRating, int $positivRecordRating): void
    {
        $ratingReportRepository = $this->getUserRecordsPositiveRatingReportRepository($positivRecordRating, 0, 0, 0);

        $this->assertExpectedUserRatingForPeriod($expectedRating, $ratingReportRepository);
    }

    public function getUserRatingDataForVideo(): Generator
    {
        yield [0, 0];

        yield [1, 1];

        yield [2, 2];
    }

    /**
     * @dataProvider getUserRatingDataForGallery
     */
    public function testCalculateAllUserRatingForUserOnlyWithGalleryRecord(int $expectedRating, int $positivRecordRating): void
    {
        $ratingReportRepository = $this->getUserRecordsPositiveRatingReportRepository(0, $positivRecordRating, 0, 0);

        $this->assertExpectedUserRatingForAllPeriod($expectedRating, $ratingReportRepository);
    }

    /**
     * @dataProvider getUserRatingDataForGallery
     */
    public function testCalculateRatingForUserOnlyWithGalleryRecord(int $expectedRating, int $positivRecordRating): void
    {
        $ratingReportRepository = $this->getUserRecordsPositiveRatingReportRepository(0, $positivRecordRating, 0, 0);

        $this->assertExpectedUserRatingForPeriod($expectedRating, $ratingReportRepository);
    }

    public function getUserRatingDataForGallery(): Generator
    {
        yield [0, 0];

        yield [1, 1];

        yield [2, 2];
    }

    /**
     * @dataProvider getUserRatingDataForTidings
     */
    public function testCalculateAllUserRatingForUserOnlyWithTidingsRecord(int $expectedRating, int $positivRecordRating): void
    {
        $ratingReportRepository = $this->getUserRecordsPositiveRatingReportRepository(0, 0, $positivRecordRating, 0);

        $this->assertExpectedUserRatingForAllPeriod($expectedRating, $ratingReportRepository);
    }

    /**
     * @dataProvider getUserRatingDataForTidings
     */
    public function testCalculateRatingForUserOnlyWithTidingsRecord(int $expectedRating, int $positivRecordRating): void
    {
        $ratingReportRepository = $this->getUserRecordsPositiveRatingReportRepository(0, 0, $positivRecordRating, 0);

        $this->assertExpectedUserRatingForPeriod($expectedRating, $ratingReportRepository);
    }

    public function getUserRatingDataForTidings(): Generator
    {
        yield [0, 0];

        yield [2, 1];

        yield [4, 2];
    }

    /**
     * @dataProvider getUserRatingDataForArticle
     */
    public function testCalculateAllUserRatingForUserOnlyWithArticleRecord(int $expectedRating, int $positivRecordRating): void
    {
        $ratingReportRepository = $this->getUserRecordsPositiveRatingReportRepository(0, 0, 0, $positivRecordRating);

        $this->assertExpectedUserRatingForAllPeriod($expectedRating, $ratingReportRepository);
    }

    /**
     * @dataProvider getUserRatingDataForArticle
     */
    public function testCalculateRatingForUserOnlyWithArticleRecord(int $expectedRating, int $positivRecordRating): void
    {
        $ratingReportRepository = $this->getUserRecordsPositiveRatingReportRepository(0, 0, 0, $positivRecordRating);

        $this->assertExpectedUserRatingForPeriod($expectedRating, $ratingReportRepository);
    }

    public function getUserRatingDataForArticle(): Generator
    {
        yield [0, 0];

        yield [5, 1];

        yield [10, 2];
    }

    /**
     * @dataProvider getUserRatingDataForForumLikes
     */
    public function testCalculateAllUserRatingForUserOnlyWithForumLike(int $expectedRating, int $countThanks): void
    {
        $ratingReportRepository = $this->getUserRecordsPositiveRatingReportRepository(0, 0, 0, 0);
        $this->forumApi = $this->getForumApi($countThanks);

        $this->assertExpectedUserRatingForAllPeriod($expectedRating, $ratingReportRepository);
    }

    /**
     * @dataProvider getUserRatingDataForForumLikes
     */
    public function testCalculateRatingForUserOnlyWithForumLike(int $expectedRating, int $countThanks): void
    {
        $ratingReportRepository = $this->getUserRecordsPositiveRatingReportRepository(0, 0, 0, 0);
        $this->forumApi = $this->getForumApi($countThanks);

        $this->assertExpectedUserRatingForPeriod($expectedRating, $ratingReportRepository);
    }

    public function getUserRatingDataForForumLikes(): Generator
    {
        yield [0, 0];

        yield [3, 1];

        yield [6, 2];
    }

    /**
     * @dataProvider getUserRatingDataForAllMaterial
     */
    public function testCalculateAllUserRatingForUserWithAllMaterials(int $expectedRating, int $commonRating): void
    {
        $ratingReportRepository = $this->getUserRecordsPositiveRatingReportRepository($commonRating, $commonRating, $commonRating, $commonRating);
        $this->forumApi = $this->getForumApi($commonRating);

        $this->assertExpectedUserRatingForAllPeriod($expectedRating, $ratingReportRepository);
    }

    /**
     * @dataProvider getUserRatingDataForAllMaterial
     */
    public function testCalculateRatingForUserWithAllMaterials(int $expectedRating, int $commonRating): void
    {
        $ratingReportRepository = $this->getUserRecordsPositiveRatingReportRepository($commonRating, $commonRating, $commonRating, $commonRating);
        $this->forumApi = $this->getForumApi($commonRating);

        $this->assertExpectedUserRatingForPeriod($expectedRating, $ratingReportRepository);
    }

    public function getUserRatingDataForAllMaterial(): Generator
    {
        yield [0, 0];

        yield [12, 1];

        yield [24, 2];
    }

    private function assertExpectedUserRatingForPeriod(int $expectedRating, UserRecordsPositiveRatingReportRepository $ratingReportRepository): void
    {
        $userRatingCalculator = new UserRatingCalculator($this->forumApi, $ratingReportRepository);

        $this->assertEquals($expectedRating, $userRatingCalculator->calculateUserRatingForPeriod($this->getUser(42), self::getDatePeriod()));
    }

    private function assertExpectedUserRatingForAllPeriod(int $expectedRating, UserRecordsPositiveRatingReportRepository $ratingReportRepository): void
    {
        $userRatingCalculator = new UserRatingCalculator($this->forumApi, $ratingReportRepository);

        $this->assertEquals($expectedRating, $userRatingCalculator->calculateUserRatingForAllPeriod($this->getUser(42)));
    }

    private static function getDatePeriod(): DatePeriod
    {
        $dateInterval = new DateInterval('P1D');

        $today = new DateTimeImmutable();
        $periodStart = $today->sub($dateInterval);

        return new DatePeriod($periodStart, $dateInterval, $today);
    }

    private function getUser(int $userId): User
    {
        $user = $this->createMock(User::class);

        $user
            ->method('getId')
            ->willReturn($userId);

        return $user;
    }

    private function getUserRecordsPositiveRatingReportRepository(int $videoRating, int $galleryRating, int $tidingRating, int $articleRating): UserRecordsPositiveRatingReportRepository
    {
        $repository = $this->createMock(UserRecordsPositiveRatingReportRepository::class);

        $repository
            ->method('getPositiveRatingReportForUserByPeriod')
            ->willReturnCallback(
                function (User $user, DateTimeInterface $periodStart, DateTimeInterface $periodEnd) use ($videoRating, $galleryRating, $tidingRating, $articleRating): AggregateUserPositiveRatingReport {
                    return new AggregateUserPositiveRatingReport(
                        $this->getUserRecordsPositiveRatingReport($user, $videoRating, $periodStart, $periodEnd),
                        $this->getUserRecordsPositiveRatingReport($user, $galleryRating, $periodStart, $periodEnd),
                        $this->getUserRecordsPositiveRatingReport($user, $tidingRating, $periodStart, $periodEnd),
                        $this->getUserRecordsPositiveRatingReport($user, $articleRating, $periodStart, $periodEnd),
                    );
                }
            );

        $repository
            ->method('getPositiveRatingReportForUserByAllPeriod')
            ->willReturnCallback(
                function (User $user) use ($videoRating, $galleryRating, $tidingRating, $articleRating): AggregateUserPositiveRatingReport {
                    return new AggregateUserPositiveRatingReport(
                        $this->getUserRecordsPositiveRatingReport($user, $videoRating, null, null),
                        $this->getUserRecordsPositiveRatingReport($user, $galleryRating, null, null),
                        $this->getUserRecordsPositiveRatingReport($user, $tidingRating, null, null),
                        $this->getUserRecordsPositiveRatingReport($user, $articleRating, null, null),
                    );
                }
            );

        return $repository;
    }

    private function getUserRecordsPositiveRatingReport(
        User $user,
        int $positiveRating,
        ?DateTimeInterface $periodStart,
        ?DateTimeInterface $periodEnd
    ): UserRecordsPositiveRatingReport {
        return new UserRecordsPositiveRatingReport($user, 'type', $positiveRating, $periodStart, $periodEnd);
    }

    private function getForumApi(int $countForumLikes): ForumApiInterface
    {
        $statisticForumApi = $this->createMock(StatisticsProviderInterface::class);

        $statisticForumApi
            ->method('getCountThanksForPeriod')
            ->willReturn($countForumLikes);

        $statisticForumApi
            ->method('getCountThanks')
            ->willReturn($countForumLikes);

        $forumApi = $this->createMock(ForumApiInterface::class);

        $forumApi
            ->method('statistics')
            ->willReturn($statisticForumApi);

        return $forumApi;
    }
}
