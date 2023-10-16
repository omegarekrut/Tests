<?php

namespace Tests\Unit\Domain\Seo\Extension\Routing;

use App\Domain\Record\Common\View\UserContentStatistic;
use App\Domain\Seo\Extension\Routing\UserProfileExtension;
use App\Domain\Seo\Factory\UserProfileSeoFactory;
use App\Domain\User\Entity\ValueObject\FishingInformation;
use App\Domain\User\View\UserProfile\UserProfileView;
use App\Module\Seo\Extension\Routing\Route;
use App\Module\Seo\Extension\SeoContext;
use App\Module\Seo\TransferObject\SeoPage;
use App\Util\Pluralization\Pluralization;
use Tests\Traits\RouteExtensionTrait;
use Tests\Unit\TestCase;

/**
 * @group profile
 * @group public-profile
 */
class UserProfileExtensionTest extends TestCase
{
    use RouteExtensionTrait;

    private const TITLE_FOR_USER_PROFILE_PAGE = 'Title for user profile page';
    private const DESCRIPTION_FOR_USER_PROFILE_PAGE = 'Description for user profile page';

    /**
     * @var SeoPage
     */
    private $seoPage;

    /**
     * @var Pluralization
     */
    private $pluralization;

    /**
     * @var UserProfileExtension
     */
    private $userBarsRouteExtension;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seoPage = new SeoPage();
        $this->pluralization = new Pluralization();

        $this->userBarsRouteExtension = new UserProfileExtension(
            $this->createBreadcrumbsFactoryMock(),
            $this->createUrlGeneratorMock(),
            $this->pluralization,
            $this->getUserProfileSeoFactory()
        );
    }

    protected function tearDown(): void
    {
        unset(
            $this->seoPage,
            $this->pluralization,
            $this->userBarsRouteExtension
        );

        parent::tearDown();
    }

    /**
     * @dataProvider getRoutesForCheckSupports
     */
    public function testIsSupportedRoutes(string $routeName, bool $expectedIsSupported): void
    {
        $route = $this->createConfiguredMock(Route::class, [
            'getName' => $routeName,
        ]);

        $this->assertEquals($expectedIsSupported, $this->userBarsRouteExtension->isSupported($route));
    }

    public function getRoutesForCheckSupports(): \Generator
    {
        yield ['user_profile', true];

        yield ['user_profile_pagination', true];

        yield ['user_profile_tidings', true];

        yield ['user_profile_tidings_pagination', true];

        yield ['user_profile_gallery', true];

        yield ['user_profile_gallery_pagination', true];

        yield ['user_profile_video', true];

        yield ['user_profile_video_pagination', true];

        yield ['user_profile_articles', true];

        yield ['user_profile_articles_pagination', true];

        yield ['user_profile_maps', true];

        yield ['user_profile_maps_pagination', true];

        yield ['user_profile_tackle_reviews', true];

        yield ['user_profile_pagination_tackle_reviews', true];

        yield ['user_profile_comment', true];

        yield ['user_profile_comment_pagination', true];

        yield ['unsupported_route', false];
    }

    /**
     * @dataProvider getSeoInformationForSeoPage
     */
    public function testGeneratedSeoPageForProfilePage(
        string $routeName,
        UserProfileView $userProfileView,
        string $expectedTitle,
        string $expectedDescription,
        int $expectedCountBreadcrumbs
    ): void {
        $route = $this->createConfiguredMock(Route::class, [
            'getName' => $routeName,
        ]);

        $seoContext = new SeoContext([$route, $userProfileView]);

        $this->userBarsRouteExtension->apply($this->seoPage, $seoContext);

        $this->assertEquals($expectedTitle, $this->seoPage->getTitle());
        $this->assertEquals($expectedDescription, $this->seoPage->getDescription());
        $this->assertEquals($expectedCountBreadcrumbs, count($this->seoPage->getBreadcrumbs()));
    }

    public function getSeoInformationForSeoPage(): \Generator
    {
        $userProfile = new UserProfileView();
        $userProfile->userId = 123;
        $userProfile->username = 'test';
        $userProfile->createdAt = new \DateTime('2011-01-01 15:03:01');
        $userProfile->fishingInformation = new FishingInformation();
        $userProfile->userContentStatistic = new UserContentStatistic();

        yield [
            'user_profile',
            $userProfile,
            self::TITLE_FOR_USER_PROFILE_PAGE,
            self::DESCRIPTION_FOR_USER_PROFILE_PAGE,
            1,
        ];

        yield [
            'user_profile_pagination',
            $userProfile,
            self::TITLE_FOR_USER_PROFILE_PAGE,
            self::DESCRIPTION_FOR_USER_PROFILE_PAGE,
            1,
        ];

        yield [
            'user_profile_tidings',
            $userProfile,
            'Вести о рыбалке пользователя test.',
            'Вести с водоемов пользователя test.',
            2,
        ];

        yield [
            'user_profile_tidings_pagination',
            $userProfile,
            'Вести о рыбалке пользователя test.',
            'Вести с водоемов пользователя test.',
            2,
        ];

        $userStatisticsWithTidingInformation = new UserContentStatistic();
        $userStatisticsWithTidingInformation->tidingCount = 1;
        $userStatisticsWithTidingInformation->dateLastTiding = new \DateTime('2011-01-01 15:03:01');

        $userProfileWithTidings = clone $userProfile;
        $userProfileWithTidings->userContentStatistic = $userStatisticsWithTidingInformation;

        yield [
            'user_profile_tidings',
            $userProfileWithTidings,
            'Вести о рыбалке пользователя test.',
            'Вести с водоемов пользователя test. Всего вестей 1. Последнее обновление 1 января 2011',
            2,
        ];

        yield [
            'user_profile_gallery',
            $userProfile,
            'Рыбацкие фотографии пользователя test.',
            'Рыбацкие фотографии пользователя test.',
            2,
        ];

        yield [
            'user_profile_gallery_pagination',
            $userProfile,
            'Рыбацкие фотографии пользователя test.',
            'Рыбацкие фотографии пользователя test.',
            2,
        ];

        $userStatisticsWithOneGalleryInformation = new UserContentStatistic();
        $userStatisticsWithOneGalleryInformation->galleryCount = 1;
        $userStatisticsWithOneGalleryInformation->dateLastGallery = new \DateTime('2011-01-01 15:03:01');

        $userProfileWithOneGallery = clone $userProfile;
        $userProfileWithOneGallery->userContentStatistic = $userStatisticsWithOneGalleryInformation;

        yield [
            'user_profile_gallery',
            $userProfileWithOneGallery,
            'Рыбацкие фотографии пользователя test.',
            'Рыбацкие фотографии пользователя test. Всего добавлено 1 изображение. Последнее фото размещено 1 января 2011.',
            2,
        ];

        $userStatisticsWithFiveGalleryInformation = new UserContentStatistic();
        $userStatisticsWithFiveGalleryInformation->galleryCount = 5;
        $userStatisticsWithFiveGalleryInformation->dateLastGallery = new \DateTime('2011-01-01 15:03:01');

        $userProfileWithFiveGallery = clone $userProfile;
        $userProfileWithFiveGallery->userContentStatistic = $userStatisticsWithFiveGalleryInformation;

        yield [
            'user_profile_gallery',
            $userProfileWithFiveGallery,
            'Рыбацкие фотографии пользователя test.',
            'Рыбацкие фотографии пользователя test. Всего добавлено 5 изображений. Последнее фото размещено 1 января 2011.',
            2,
        ];

        $userStatisticsWithTwoGalleryInformation = new UserContentStatistic();
        $userStatisticsWithTwoGalleryInformation->galleryCount = 2;
        $userStatisticsWithTwoGalleryInformation->dateLastGallery = new \DateTime('2011-01-01 15:03:01');

        $userProfileWithTwoGallery = clone $userProfile;
        $userProfileWithTwoGallery->userContentStatistic = $userStatisticsWithTwoGalleryInformation;

        yield [
            'user_profile_gallery',
            $userProfileWithTwoGallery,
            'Рыбацкие фотографии пользователя test.',
            'Рыбацкие фотографии пользователя test. Всего добавлено 2 изображения. Последнее фото размещено 1 января 2011.',
            2,
        ];

        yield [
            'user_profile_video',
            $userProfile,
            'Видео о рыбалке пользователя test.',
            'Видео о рыбалке, добавленное пользователем test.',
            2,
        ];

        yield [
            'user_profile_video_pagination',
            $userProfile,
            'Видео о рыбалке пользователя test.',
            'Видео о рыбалке, добавленное пользователем test.',
            2,
        ];

        $userStatisticsWithVideoInformation = new UserContentStatistic();
        $userStatisticsWithVideoInformation->videoCount = 1;
        $userStatisticsWithVideoInformation->dateLastVideo = new \DateTime('2011-01-01 15:03:01');

        $userProfileWithVideo = clone $userProfile;
        $userProfileWithVideo->userContentStatistic = $userStatisticsWithVideoInformation;

        yield [
            'user_profile_video',
            $userProfileWithVideo,
            'Видео о рыбалке пользователя test.',
            'Видео о рыбалке, добавленное пользователем test. Всего видеороликов, размещенных на сайте 1. Последнее обновление коллекции видео 1 января 2011.',
            2,
        ];

        yield [
            'user_profile_articles',
            $userProfile,
            'Записи пользователя test.',
            'Записи пользователя test.',
            2,
        ];

        yield [
            'user_profile_articles_pagination',
            $userProfile,
            'Записи пользователя test.',
            'Записи пользователя test.',
            2,
        ];

        $userStatisticsWithArticleInformation = new UserContentStatistic();
        $userStatisticsWithArticleInformation->articleCount = 1;
        $userStatisticsWithArticleInformation->dateLastArticle = new \DateTime('2011-01-01 15:03:01');

        $userProfileWithArticle = clone $userProfile;
        $userProfileWithArticle->userContentStatistic = $userStatisticsWithArticleInformation;

        yield [
            'user_profile_articles',
            $userProfileWithArticle,
            'Записи пользователя test.',
            'Записи пользователя test. Всего 1 запись о рыбалке, снастях и снаряжении. Последняя запись от 1 января 2011.',
            2,
        ];

        $userStatisticsWithTwoArticleInformation = new UserContentStatistic();
        $userStatisticsWithTwoArticleInformation->articleCount = 2;
        $userStatisticsWithTwoArticleInformation->dateLastArticle = new \DateTime('2011-01-01 15:03:01');

        $userProfileWithTwoArticle = clone $userProfile;
        $userProfileWithTwoArticle->userContentStatistic = $userStatisticsWithTwoArticleInformation;

        yield [
            'user_profile_articles',
            $userProfileWithTwoArticle,
            'Записи пользователя test.',
            'Записи пользователя test. Всего 2 записи о рыбалке, снастях и снаряжении. Последняя запись от 1 января 2011.',
            2,
        ];

        $userStatisticsWithFiveArticleInformation = new UserContentStatistic();
        $userStatisticsWithFiveArticleInformation->articleCount = 5;
        $userStatisticsWithFiveArticleInformation->dateLastArticle = new \DateTime('2011-01-01 15:03:01');

        $userProfileWithFiveArticle = clone $userProfile;
        $userProfileWithFiveArticle->userContentStatistic = $userStatisticsWithFiveArticleInformation;

        yield [
            'user_profile_articles',
            $userProfileWithFiveArticle,
            'Записи пользователя test.',
            'Записи пользователя test. Всего 5 записей о рыбалке, снастях и снаряжении. Последняя запись от 1 января 2011.',
            2,
        ];

        yield [
            'user_profile_maps',
            $userProfile,
            'Карты пользователя test.',
            'Карты с местами удачной рыбалки, добавленные пользователем test.',
            2,
        ];

        yield [
            'user_profile_maps_pagination',
            $userProfile,
            'Карты пользователя test.',
            'Карты с местами удачной рыбалки, добавленные пользователем test.',
            2,
        ];

        $userProfileWithFishingTime = clone $userProfile;
        $userProfileWithFishingTime->fishingInformation = new FishingInformation(null, null, null, 'В основном летом');

        yield [
            'user_profile_maps',
            $userProfileWithFishingTime,
            'Карты пользователя test.',
            'Карты с местами удачной рыбалки, добавленные пользователем test. Рыбачит в основном летом.',
            2,
        ];

        $userStatisticsWithMapInformation = new UserContentStatistic();
        $userStatisticsWithMapInformation->mapCount = 1;
        $userStatisticsWithMapInformation->dateLastMap = new \DateTime('2011-01-01 15:03:01');

        $userProfileWithMapInformation = clone $userProfile;
        $userProfileWithMapInformation->userContentStatistic = $userStatisticsWithMapInformation;

        yield [
            'user_profile_maps',
            $userProfileWithMapInformation,
            'Карты пользователя test.',
            'Карты с местами удачной рыбалки, добавленные пользователем test. Всего рыболовных карт 1. Последнее обновление 1 января 2011.',
            2,
        ];

        $userProfileWithFishingTimeInformationAndMaps = clone $userProfileWithFishingTime;
        $userProfileWithFishingTimeInformationAndMaps->userContentStatistic = $userStatisticsWithMapInformation;

        yield [
            'user_profile_maps',
            $userProfileWithFishingTimeInformationAndMaps,
            'Карты пользователя test.',
            'Карты с местами удачной рыбалки, добавленные пользователем test. Рыбачит в основном летом. Всего рыболовных карт 1. Последнее обновление 1 января 2011.',
            2,
        ];

        yield [
            'user_profile_tackle_reviews',
            $userProfile,
            'Отзывы о рыболовных снастях и снаряжении пользователя test.',
            'Отзывы пользователя test о рыболовных снастях и снаряжении.',
            2,
        ];

        yield [
            'user_profile_pagination_tackle_reviews',
            $userProfile,
            'Отзывы о рыболовных снастях и снаряжении пользователя test.',
            'Отзывы пользователя test о рыболовных снастях и снаряжении.',
            2,
        ];

        $userStatisticsWithTackleReviewInformation = new UserContentStatistic();
        $userStatisticsWithTackleReviewInformation->tackleReviewCount = 1;
        $userStatisticsWithTackleReviewInformation->dateLastTackleReview = new \DateTime('2011-01-01 15:03:01');

        $userProfileWithTackleReviewInformation = clone $userProfile;
        $userProfileWithTackleReviewInformation->userContentStatistic = $userStatisticsWithTackleReviewInformation;

        yield [
            'user_profile_tackle_reviews',
            $userProfileWithTackleReviewInformation,
            'Отзывы о рыболовных снастях и снаряжении пользователя test.',
            'Отзывы пользователя test о рыболовных снастях и снаряжении. Всего отзывов: 1. Последний отзыв добавлен 1 января 2011.',
            2,
        ];

        yield [
            'user_profile_comment',
            $userProfile,
            'Комментарии пользователя test',
            'Все комментарии пользователя test на рыболовном портале FishingSib.ru с момента его регистрации на сайте с 1 января 2011.',
            2,
        ];

        yield [
            'user_profile_comment_pagination',
            $userProfile,
            'Комментарии пользователя test',
            'Все комментарии пользователя test на рыболовном портале FishingSib.ru с момента его регистрации на сайте с 1 января 2011.',
            2,
        ];

        $userStatisticsWithCommentInformation = new UserContentStatistic();
        $userStatisticsWithCommentInformation->commentCount = 1;

        $userProfileWithCommentInformation = clone $userProfile;
        $userProfileWithCommentInformation->userContentStatistic = $userStatisticsWithCommentInformation;

        yield [
            'user_profile_comment',
            $userProfileWithCommentInformation,
            'Комментарии пользователя test',
            'Все комментарии пользователя test на рыболовном портале FishingSib.ru с момента его регистрации на сайте с 1 января 2011. Всего комментариев: 1.',
            2,
        ];
    }

    private function getUserProfileSeoFactory(): UserProfileSeoFactory
    {
        $userProfileSeoFactory = $this->createMock(UserProfileSeoFactory::class);

        $userProfileSeoFactory
            ->method('createTitleForUserProfilePage')
            ->willReturn(self::TITLE_FOR_USER_PROFILE_PAGE);

        $userProfileSeoFactory
            ->method('createDescriptionForUserProfilePage')
            ->willReturn(self::DESCRIPTION_FOR_USER_PROFILE_PAGE);

        return $userProfileSeoFactory;
    }
}
