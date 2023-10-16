<?php

namespace Tests\Unit\Auth\Visitor\Profile;

use App\Auth\Visitor\Profile\ProfileProxy;
use App\Bridge\Xenforo\ForumApiInterface;
use App\Bridge\Xenforo\Provider\ProfileProviderInterface;
use App\Bridge\Xenforo\RemoteObject\ForumUserProfile;
use App\Domain\User\Collection\NotificationCollection;
use App\Domain\User\Entity\Notification\ForumNotification;
use App\Domain\User\Entity\Notification\Notification;
use App\Domain\User\Entity\Notification\ValueObject\NotificationCategory;
use App\Domain\User\Entity\User;
use App\Domain\User\View\UserProfile\UserProfileView;
use App\Module\AggregatedNotification\NotificationAggregator;
use App\Module\ObjectPropertiesFilledCalculator\ObjectPropertiesFilledCalculator;
use Tests\Unit\TestCase;

/**
 * @group profile
 * @group visitor
 */
class ProfileProxyTest extends TestCase
{
    private const EXPECTED_PERCENTAGE_OF_COMPLETED = 50;
    private const EXPECTED_FORUM_USER_ID = 1;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = $this->createUser();
    }

    public function testPercentageOfCompleted(): void
    {
        $percentageOfCompletedCalculator = $this->createPercentageOfCompletedCalculatorForOnceCall($this->user, self::EXPECTED_PERCENTAGE_OF_COMPLETED);

        $profile = new ProfileProxy(
            $this->createMock(ForumApiInterface::class),
            $percentageOfCompletedCalculator,
            $this->createMock(NotificationAggregator::class)
        );
        $profile = $profile->withUser($this->user);

        self::repeatTwice(function () use ($profile): void {
            $this->assertEquals(self::EXPECTED_PERCENTAGE_OF_COMPLETED, $profile->getPercentageOfCompleted());
        });
    }

    public function testForumProfile(): void
    {
        $forumUserProfile = new ForumUserProfile();
        $forumUserProfile->smallAvatar = 'http://image';
        $forumUserProfile->unreadMessagesCount = 20;

        $forumApi = $this->createForumApiWithProfileProviderForOneCall(self::EXPECTED_FORUM_USER_ID, $forumUserProfile);
        $profile = new ProfileProxy(
            $forumApi,
            $this->createMock(ObjectPropertiesFilledCalculator::class),
            $this->createMock(NotificationAggregator::class)
        );
        $profile = $profile->withUser($this->user);

        self::repeatTwice(function () use ($profile, $forumUserProfile): void {
            $this->assertEquals($forumUserProfile->smallAvatar, $profile->getAvatar());
            $this->assertEquals($forumUserProfile->unreadMessagesCount, $profile->getCountUnreadPrivateMessages());
        });
    }

    public function testProfileShouldContainsNotificationsCount(): void
    {
        $profile = new ProfileProxy(
            $this->createMock(ForumApiInterface::class),
            $this->createMock(ObjectPropertiesFilledCalculator::class),
            $this->createNotificationAggregatorForResultNotifications([
                $this->createMock(ForumNotification::class),
                $this->createMock(ForumNotification::class),
            ])
        );

        $profile = $profile->withUser($this->user);

        $this->assertEquals(2, $profile->getCountNotification());
    }

    public function testGuestProfile(): void
    {
        $profile = new ProfileProxy(
            $this->createMock(ForumApiInterface::class),
            $this->createMock(ObjectPropertiesFilledCalculator::class),
            $this->createMock(NotificationAggregator::class)
        );

        $this->assertEquals(0, $profile->getPercentageOfCompleted());
        $this->assertEquals('/img/icon/user.svg', $profile->getAvatar());
        $this->assertEquals(0, $profile->getCountNotification());
        $this->assertEquals(0, $profile->getCountUnreadPrivateMessages());
    }

    private static function repeatTwice(callable $callback): void
    {
        $callback();
        $callback();
    }

    private function createUser(): User
    {
        $stub = $this->createMock(User::class);
        $stub
            ->method('getForumUserId')
            ->willReturn(self::EXPECTED_FORUM_USER_ID);
        $stub
            ->method('getUnreadNotifications')
            ->willReturn(new NotificationCollection([
                new ForumNotification(1, 'some message', NotificationCategory::like(), $this->createMock(User::class)),
            ]));

        return $stub;
    }

    private function createPercentageOfCompletedCalculatorForOnceCall(User $user, int $percentageOfCompleted): ObjectPropertiesFilledCalculator
    {
        $stub = $this->createMock(ObjectPropertiesFilledCalculator::class);
        $stub
            ->expects($this->once())
            ->method('calculatePercentage')
            ->with($user, UserProfileView::USER_CHECK_PROPERTIES_FOR_COMPLETION)
            ->willReturn($percentageOfCompleted);

        return $stub;
    }

    private function createForumApiWithProfileProviderForOneCall(int $forumUserId, ForumUserProfile $forumUserProfile): ForumApiInterface
    {
        $profileProvider = $this->createMock(ProfileProviderInterface::class);
        $profileProvider
            ->expects($this->once())
            ->method('getProfile')
            ->with($forumUserId)
            ->willReturn($forumUserProfile);

        $forumApi = $this->createMock(ForumApiInterface::class);
        $forumApi
            ->expects($this->once())
            ->method('profile')
            ->willReturn($profileProvider);

        return $forumApi;
    }

    /**
     * @param Notification[] $notifications
     */
    private function createNotificationAggregatorForResultNotifications(array $notifications): NotificationAggregator
    {
        $stub = $this->createMock(NotificationAggregator::class);
        $stub
            ->method('aggregate')
            ->willReturnCallback(static function () use ($notifications) {
                foreach ($notifications as $notification) {
                    yield $notification;
                }
            });

        return $stub;
    }
}
