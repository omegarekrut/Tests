<?php

namespace Tests\Unit\Module\Author\View;

use App\Domain\Comment\Entity\Comment;
use App\Domain\Company\Entity\Company;
use App\Domain\Company\HasCompanyAuthorInterface;
use App\Domain\Record\Common\Entity\Record;
use App\Domain\User\Collection\SubscriberCollection;
use App\Domain\User\Entity\User;
use App\Domain\User\Entity\ValueObject\Rating;
use App\Module\Author\AuthorInterface;
use App\Module\Author\View\AuthorAvatarView;
use App\Module\Author\View\AuthorAvatarViewFactory;
use App\Module\Author\View\AuthorView;
use App\Module\Author\View\AuthorViewFactory;
use InvalidArgumentException;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Routing\RouterInterface;
use Tests\Unit\TestCase;

class AuthorViewFactoryTest extends TestCase
{
    private const ROUTER_GENERATED_URL = 'some_url';
    private const EXPECTED_USER_ACTIVITY_RATING = 21;
    private const EXPECTED_USER_GLOBAL_RATING = 42;

    private AuthorViewFactory $authorViewFactory;
    private AuthorAvatarView $expectedAvatarObjectForUser;
    private AuthorAvatarView $expectedAvatarObjectForAnonymousUser;
    private AuthorAvatarView $expectedAvatarObjectForCompany;
    private AuthorAvatarView $expectedAvatarObjectForAnonymousCompany;

    protected function setUp(): void
    {
        parent::setUp();

        $this->expectedAvatarObjectForUser = new AuthorAvatarView();
        $this->expectedAvatarObjectForUser->withSmallSide = 'user_avatar.jpeg';

        $this->expectedAvatarObjectForAnonymousUser = new AuthorAvatarView();
        $this->expectedAvatarObjectForAnonymousUser->withSmallSide = 'anonymous_user_avatar.jpeg';

        $this->expectedAvatarObjectForCompany = new AuthorAvatarView();
        $this->expectedAvatarObjectForCompany->withSmallSide = 'company_avatar.jpeg';

        $this->expectedAvatarObjectForAnonymousCompany = new AuthorAvatarView();
        $this->expectedAvatarObjectForAnonymousCompany->withSmallSide = 'anonymous_company_avatar.jpeg';

        $this->authorViewFactory = new AuthorViewFactory(
            $this->getRouter(),
            $this->getAvatarViewFactory()
        );
    }

    protected function tearDown(): void
    {
        unset(
            $this->authorViewFactory,
            $this->expectedAvatarObjectForUser,
            $this->expectedAvatarObjectForAnonymousUser,
            $this->expectedAvatarObjectForCompany,
            $this->expectedAvatarObjectForAnonymousCompany,
        );

        parent::tearDown();
    }

    public function testCreateFromHasCompanyAuthorInterfaceWithCompany(): void
    {
        $company = $this->createCompany();

        $hasCompanyInterface = $this->createMock(HasCompanyAuthorInterface::class);
        $hasCompanyInterface->method('getCompanyAuthorName')->willReturn(null);
        $hasCompanyInterface->method('getCompanyAuthor')->willReturn($company);
        $hasCompanyInterface->method('companyAuthorIsPublic')->willReturn(true);

        $authorView = $this->authorViewFactory->createFromHasCompanyAuthorInterface($hasCompanyInterface);

        $this->assertEquals($company->getId(), $authorView->id);
        $this->assertEquals($company->getName(), $authorView->name);
        $this->assertEquals($this->expectedAvatarObjectForCompany, $authorView->avatar);
        $this->assertEquals($company->getSubscribers(), $authorView->subscribers);
        $this->assertEquals(0, $authorView->globalRating);
        $this->assertEquals(0, $authorView->activityRating);
    }

    public function testCreateFromHasCompanyAuthorInterfaceWithAnonymousCompany(): void
    {
        $expectedCompanyName = 'Not exist Company';

        $hasCompanyInterface = $this->createMock(HasCompanyAuthorInterface::class);
        $hasCompanyInterface->method('getCompanyAuthorName')->willReturn($expectedCompanyName);
        $hasCompanyInterface->method('getCompanyAuthor')->willReturn(null);

        $authorView = $this->authorViewFactory->createFromHasCompanyAuthorInterface($hasCompanyInterface);

        $this->assertNull($authorView->id);
        $this->assertEquals($expectedCompanyName, $authorView->name);
        $this->assertEquals($this->expectedAvatarObjectForAnonymousCompany, $authorView->avatar);
        $this->assertEquals(0, $authorView->globalRating);
        $this->assertEquals(0, $authorView->activityRating);
    }

    public function testCreateFromHasCompanyAuthorInterfaceRecordWithHasAuthorInterface(): void
    {
        $user = $this->createUser();

        $record = $this->createMock(Record::class);
        $record->method('getAuthor')
            ->willReturn($user);

        $authorView = $this->authorViewFactory->createFromHasCompanyAuthorInterface($record);

        $this->assertInstanceOf(AuthorView::class, $authorView);

        $this->assertEquals($user->getId(), $authorView->id);
        $this->assertEquals($user->getUsername(), $authorView->name);
        $this->assertEquals($this->expectedAvatarObjectForUser, $authorView->avatar);
        $this->assertEquals($user->getSubscribers(), $authorView->subscribers);
        $this->assertEquals($user->getCityName(), $authorView->city);
        $this->assertEquals(self::EXPECTED_USER_GLOBAL_RATING, $authorView->globalRating);
        $this->assertEquals(self::EXPECTED_USER_ACTIVITY_RATING, $authorView->activityRating);
    }

    public function testCreateFromHasCompanyAuthorInterfaceCommentWithHasAuthorInterface(): void
    {
        $user = $this->createUser();

        $record = $this->createMock(Comment::class);
        $record->method('getAuthor')
            ->willReturn($user);

        $authorView = $this->authorViewFactory->createFromHasCompanyAuthorInterface($record);

        $this->assertInstanceOf(AuthorView::class, $authorView);

        $this->assertEquals($user->getId(), $authorView->id);
        $this->assertEquals($user->getUsername(), $authorView->name);
        $this->assertEquals($this->expectedAvatarObjectForUser, $authorView->avatar);
        $this->assertEquals($user->getSubscribers(), $authorView->subscribers);
        $this->assertEquals($user->getCityName(), $authorView->city);
        $this->assertEquals(self::EXPECTED_USER_GLOBAL_RATING, $authorView->globalRating);
        $this->assertEquals(self::EXPECTED_USER_ACTIVITY_RATING, $authorView->activityRating);
    }

    public function testCreateFromHasCompanyAuthorInterfaceWithInvalidInformation(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $hasCompanyInterface = $this->createMock(HasCompanyAuthorInterface::class);
        $hasCompanyInterface->method('getCompanyAuthorName')->willReturn(null);
        $hasCompanyInterface->method('getCompanyAuthor')->willReturn(null);

        $this->authorViewFactory->createFromHasCompanyAuthorInterface($hasCompanyInterface);
    }

    public function testCreateFromUser(): void
    {
        $user = $this->createUser();

        $authorView = $this->authorViewFactory->createFromUser($user);

        $this->assertInstanceOf(AuthorView::class, $authorView);

        $this->assertEquals($user->getId(), $authorView->id);
        $this->assertEquals($user->getUsername(), $authorView->name);
        $this->assertEquals($this->expectedAvatarObjectForUser, $authorView->avatar);
        $this->assertEquals($user->getSubscribers(), $authorView->subscribers);
        $this->assertEquals($user->getCityName(), $authorView->city);
        $this->assertEquals(self::EXPECTED_USER_GLOBAL_RATING, $authorView->globalRating);
        $this->assertEquals(self::EXPECTED_USER_ACTIVITY_RATING, $authorView->activityRating);
    }

    public function testCreateFromCompany(): void
    {
        $company = $this->createCompany();

        $authorView = $this->authorViewFactory->createFromCompany($company);

        $this->assertInstanceOf(AuthorView::class, $authorView);

        $this->assertEquals($company->getId(), $authorView->id);
        $this->assertEquals($company->getName(), $authorView->name);
        $this->assertEquals($this->expectedAvatarObjectForCompany, $authorView->avatar);
        $this->assertEquals($company->getSubscribers(), $authorView->subscribers);
        $this->assertEquals(0, $authorView->globalRating);
        $this->assertEquals(0, $authorView->activityRating);
    }

    public function testCreateFromAuthorInterface(): void
    {
        $author = $this->createMock(AuthorInterface::class);
        $author->method('getId')
            ->willReturn(42);

        $author->method('getUsername')
            ->willReturn('anonymous');

        $author->method('getSubscribers')
            ->willReturn(new SubscriberCollection());

        $authorView = $this->authorViewFactory->createFromAuthor($author);

        $this->assertInstanceOf(AuthorView::class, $authorView);

        $this->assertEquals($author->getId(), $authorView->id);
        $this->assertEquals($author->getUsername(), $authorView->name);
        $this->assertEquals($author->getSubscribers(), $authorView->subscribers);
        $this->assertEquals($this->expectedAvatarObjectForAnonymousUser, $authorView->avatar);
    }

    private function createUser(): User
    {
        $stub = $this->createMock(User::class);
        $stub
            ->method('getId')
            ->willReturn(7777);
        $stub
            ->method('getName')
            ->willReturn('TestUserName');
        $stub
            ->method('getAvatar')
            ->willReturn(null);
        $stub
            ->method('getSubscribers')
            ->willReturn(new SubscriberCollection());
        $stub
            ->method('getGlobalRating')
            ->willReturn(new Rating(self::EXPECTED_USER_GLOBAL_RATING));
        $stub
            ->method('getActivityRating')
            ->willReturn(new Rating(self::EXPECTED_USER_ACTIVITY_RATING));
        $stub
            ->method('getCityName')
            ->willReturn('testCity');

        return $stub;
    }

    private function createCompany(): Company
    {
        $stub = $this->createMock(Company::class);
        $stub
            ->method('getId')
            ->willReturn(Uuid::uuid4());
        $stub
            ->method('getName')
            ->willReturn('TestUserName');
        $stub
            ->method('getLogoImage')
            ->willReturn(null);
        $stub
            ->method('getSubscribers')
            ->willReturn(new SubscriberCollection());

        return $stub;
    }

    private function getRouter(): RouterInterface
    {
        $router = $this->createMock(RouterInterface::class);
        $router->method('generate')
            ->willReturn(self::ROUTER_GENERATED_URL);

        return $router;
    }

    private function getAvatarViewFactory(): AuthorAvatarViewFactory
    {
        $avatarPathGenerator = $this->createMock(AuthorAvatarViewFactory::class);
        $avatarPathGenerator->method('createForUserAnonymous')
            ->willReturn($this->expectedAvatarObjectForAnonymousUser);

        $avatarPathGenerator->method('createForUser')
            ->willReturn($this->expectedAvatarObjectForUser);

        $avatarPathGenerator->method('createForCompany')
            ->willReturn($this->expectedAvatarObjectForCompany);

        $avatarPathGenerator->method('createForCompanyAnonymous')
            ->willReturn($this->expectedAvatarObjectForAnonymousCompany);

        return $avatarPathGenerator;
    }
}
