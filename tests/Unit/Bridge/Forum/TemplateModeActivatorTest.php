<?php

namespace Tests\Unit\Bridge\Forum;

use App\Auth\Visitor\Visitor;
use App\Bridge\Xenforo\Exception\UnsupportedMethodInTemplateModeException;
use App\Bridge\Xenforo\ForumApi;
use App\Bridge\Xenforo\Interfaces\BridgeUserInterface;
use App\Bridge\Xenforo\RemoteObject\ForumThread;
use App\Bridge\Xenforo\RemoteObject\ForumUser;
use App\Bridge\Xenforo\RemoteObject\ForumUserProfile;
use App\Bridge\Xenforo\RuntimeApiMode\TemplateMode\PrePopulateRequest;
use App\Bridge\Xenforo\RuntimeApiMode\TemplateMode\TemplateModeActivator;
use App\Domain\User\Entity\User;
use Carbon\Carbon;
use Tests\Unit\TestCase;

class TemplateModeActivatorTest extends TestCase
{
    private const EXPECTED_USER_ID = 1;
    private const EXPECTED_FORUM_USER_ID = 2;
    private const UNEXPECTED_FORUM_USER_ID = 999;
    private const UNEXPECTED_ROUTE_NAME = 'unexpected-route';
    private const EXPECTED_ROUTE_NAME = 'route';
    private const EXPECTED_ROUTE_URL = 'url';

    private TemplateModeActivator $templateModeActivator;
    private ForumApi $forumApi;
    private PrePopulateRequest $prePopulateRequest;
    private ForumUser $manOfTheWeek;
    private ForumUserProfile $profile;
    private ForumThread $expectedLatestThreadsForSidebar;

    protected function setUp(): void
    {
        parent::setUp();

        $this->forumApi = new ForumApi();
        $this->templateModeActivator = new TemplateModeActivator($this->forumApi, $this->createVisitor(self::EXPECTED_FORUM_USER_ID));
        $this->prePopulateRequest = new PrePopulateRequest();

        $this->manOfTheWeek = new ForumUser();
        $this->manOfTheWeek->userId = self::EXPECTED_FORUM_USER_ID;
        $this->manOfTheWeek->siteUserId = self::EXPECTED_USER_ID;

        $this->prePopulateRequest->setManOfTheWeek($this->manOfTheWeek);
        $this->prePopulateRequest->routes = [
            self::EXPECTED_ROUTE_NAME => self::EXPECTED_ROUTE_URL,
        ];

        $this->profile = new ForumUserProfile();
        $this->prePopulateRequest->setProfile($this->profile);
        $this->expectedLatestThreadsForSidebar = new ForumThread();
        $this->prePopulateRequest->addSidebarLastThreads($this->expectedLatestThreadsForSidebar);

        $this->templateModeActivator->activateWith($this->prePopulateRequest);
    }

    protected function tearDown(): void
    {
        unset(
            $this->templateModeActivator,
            $this->forumApi,
            $this->prePopulateRequest,
            $this->manOfTheWeek,
            $this->profile,
            $this->expectedLatestThreadsForSidebar
        );
    }

    public function testSupportedMethods(): void
    {
        $actualManOfTheWeek = $this->forumApi->statistics()->getManOfTheWeek();
        $this->assertEquals($this->manOfTheWeek, $actualManOfTheWeek);
        $this->assertEquals(self::EXPECTED_USER_ID, $actualManOfTheWeek->siteUserId);
        $this->assertEquals($this->manOfTheWeek->userId, $actualManOfTheWeek->userId);
        $this->assertEquals($this->manOfTheWeek->username, $actualManOfTheWeek->username);
        $this->assertEquals($this->manOfTheWeek->avatar, $actualManOfTheWeek->avatar);
        $this->assertEquals($this->manOfTheWeek->countThanks, $actualManOfTheWeek->countThanks);

        $this->assertEquals($this->profile, $this->forumApi->profile()->getProfile(self::EXPECTED_FORUM_USER_ID));

        $this->assertEquals(self::EXPECTED_ROUTE_URL, $this->forumApi->url()->getRoute(self::EXPECTED_ROUTE_NAME));

        $this->assertEquals([
            $this->expectedLatestThreadsForSidebar,
        ], $this->forumApi->threads()->getLatestForSidebar());
    }

    public function testSendWarningMessageIsUnsupported(): void
    {
        $this->expectException(UnsupportedMethodInTemplateModeException::class);

        $this->forumApi->message()->sendWarning(1, 1, 'string', 'string');
    }

    public function testDeleteProfileAvatarIsUnsupported(): void
    {
        $this->expectException(UnsupportedMethodInTemplateModeException::class);

        $this->forumApi->profile()->deleteAvatar($this->createMock(BridgeUserInterface::class));
    }

    public function testUploadProfileAvatarIsUnsupported(): void
    {
        $this->expectException(UnsupportedMethodInTemplateModeException::class);

        $this->forumApi->profile()->uploadAvatar($this->createMock(BridgeUserInterface::class), 'string');
    }

    public function testGetProfileByUnexpectedUserIdIsUnsupported(): void
    {
        $this->expectException(UnsupportedMethodInTemplateModeException::class);

        $this->forumApi->profile()->getProfile(self::UNEXPECTED_FORUM_USER_ID);
    }

    public function testCreateShopIsUnsupported(): void
    {
        $this->expectException(UnsupportedMethodInTemplateModeException::class);

        $this->forumApi->shop()->create('string', 'string');
    }

    public function testUpdateShopIsUnsupported(): void
    {
        $this->expectException(UnsupportedMethodInTemplateModeException::class);

        $this->forumApi->shop()->update(1, 'string', 'string');
    }

    public function testGetCountThanksForPeriodIsUnsupported(): void
    {
        $this->expectException(UnsupportedMethodInTemplateModeException::class);

        $this->forumApi->statistics()->getCountThanksForPeriod(1, Carbon::now()->subDay(), Carbon::now());
    }

    public function testGetLatestShoppingDiscussionsIsUnsupported(): void
    {
        $this->expectException(UnsupportedMethodInTemplateModeException::class);

        $this->forumApi->threads()->getLatestShoppingDiscussions();
    }

    public function testGetThreadsInformationIsUnsupported(): void
    {
        $this->expectException(UnsupportedMethodInTemplateModeException::class);

        $this->forumApi->threads()->getInformation([1]);
    }

    public function testGetLastPostsIsUnsupported(): void
    {
        $this->expectException(UnsupportedMethodInTemplateModeException::class);

        $this->forumApi->threads()->getLastPosts([1]);
    }

    public function testGetRouteByUnexpectedNameIsUnsupported(): void
    {
        $this->expectException(UnsupportedMethodInTemplateModeException::class);

        $this->forumApi->url()->getRoute(self::UNEXPECTED_ROUTE_NAME);
    }

    public function testUserUpdateIsUnsupported(): void
    {
        $this->expectException(UnsupportedMethodInTemplateModeException::class);

        $this->forumApi->user()->update($this->createMock(BridgeUserInterface::class));
    }

    public function testUserCreateIsUnsupported(): void
    {
        $this->expectException(UnsupportedMethodInTemplateModeException::class);

        $this->forumApi->user()->create('username', 'user@email.com', 'plain-password');
    }

    public function testDeleteSpamUserIsUnsupported(): void
    {
        $this->expectException(UnsupportedMethodInTemplateModeException::class);

        $this->forumApi->user()->deleteSpamUser(1);
    }

    public function testDeleteUserIsUnsupported(): void
    {
        $this->expectException(UnsupportedMethodInTemplateModeException::class);

        $this->forumApi->user()->deleteUser(1, true);
    }

    public function testUpdatePasswordIsUnsupported(): void
    {
        $this->expectException(UnsupportedMethodInTemplateModeException::class);

        $this->forumApi->user()->updatePassword(1, 'string');
    }

    private function createVisitor(int $forumUserID): Visitor
    {
        $user = $this->createMock(User::class);
        $user
            ->method('getForumUserId')
            ->willReturn($forumUserID);

        $visitor = $this->createMock(Visitor::class);
        $visitor
            ->method('isGuest')
            ->willReturn(false);
        $visitor
            ->method('getUser')
            ->willReturn($user);

        return $visitor;
    }
}
