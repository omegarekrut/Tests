<?php

namespace Tests\Functional\Domain\User\View\Notification;

use App\Domain\Comment\Collection\CommentCollection;
use App\Domain\Comment\Entity\Comment;
use App\Domain\Company\Entity\Company;
use App\Domain\Company\Entity\Notification\NewOwnershipRequestNotification;
use App\Domain\Company\Entity\OwnershipRequest;
use App\Domain\Company\Entity\ValueObject\LogoImage;
use App\Domain\Record\Article\Entity\Article;
use App\Domain\Record\Common\Collection\RecordSemanticLinkCollection;
use App\Domain\Record\Common\Entity\Record;
use App\Domain\Record\CompanyArticle\Entity\CompanyArticle;
use App\Domain\Record\Gallery\Entity\Gallery;
use App\Domain\Record\Map\Entity\Map;
use App\Domain\Record\Tidings\Entity\Tidings;
use App\Domain\Record\Video\Entity\Video;
use App\Domain\SuggestedNews\Entity\SuggestedNews;
use App\Domain\User\Entity\Notification\CompanyCreatedNotification;
use App\Domain\User\Entity\Notification\CompanyArticleCreatedNotification;
use App\Domain\User\Entity\Notification\ForumNotification;
use App\Domain\User\Entity\Notification\CommentOnCommentedRecordNotification;
use App\Domain\User\Entity\Notification\CommentOnRecordNotification;
use App\Domain\User\Entity\Notification\Notification;
use App\Domain\User\Entity\Notification\OwnershipRequestApprovedNotification;
use App\Domain\User\Entity\Notification\ArticleCreatedNotification;
use App\Domain\User\Entity\Notification\GalleryCreatedNotification;
use App\Domain\User\Entity\Notification\MapCreatedNotification;
use App\Domain\User\Entity\Notification\SuggestedNewsCreatedNotification;
use App\Domain\User\Entity\Notification\TidingsCreatedNotification;
use App\Domain\User\Entity\Notification\VideoCreatedNotification;
use App\Domain\User\Entity\Notification\ValueObject\NotificationCategory;
use App\Domain\User\Entity\Notification\PositiveVoteOnCommentNotification;
use App\Domain\User\Entity\Notification\PositiveVoteOnRecordNotification;
use App\Domain\User\Entity\User;
use App\Domain\User\Entity\ValueObject\Avatar;
use App\Domain\User\View\Notification\ConcreteNotificationViewFactory\NotificationLogoGenerator\NotificationLogoPathGenerator;
use App\Domain\User\View\Notification\NotificationView;
use App\Domain\User\View\Notification\UserNotificationViewsFactory;
use App\Domain\User\Entity\Notification\AggregatedNotification\AggregatedNotification;
use App\Module\Author\AuthorInterface;
use App\Module\Owner\AnonymousOwner;
use App\Module\Voting\Entity\VotableIdentifier;
use App\Module\Voting\Entity\Vote;
use App\Module\Voting\VoterInterface;
use App\Module\YoutubeVideo\Collection\YoutubeVideoUrlCollection;
use App\Util\ImageStorage\Collection\ImageCollection;
use App\Util\ImageStorage\Image;
use App\Util\Security\AssertionSubject\OwnerInterface;
use Carbon\Carbon;
use DateTime;
use Ramsey\Uuid\Uuid;
use Tests\Functional\TestCase;

/**
 * @group notification
 */
class UserNotificationViewFactoryTest extends TestCase
{
    private UserNotificationViewsFactory $notificationViewFactory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->notificationViewFactory = $this->getContainer()->get(UserNotificationViewsFactory::class);
    }

    protected function tearDown(): void
    {
        unset($this->notificationViewFactory);

        parent::tearDown();
    }

    public function testFactoryCanCreateForumNotificationView(): void
    {
        $notification = $this->createForumNotification();

        $notificationView = $this->notificationViewFactory->create($notification);

        $this->assertNotificationViewContainsBasicNotificationInformation($notification, $notificationView);

        $this->assertEquals($notification->getMessage(), $notificationView->body->htmlDescription);
        $this->assertEmpty($notificationView->body->htmlContext);
    }

    public function testFactoryCanCreateCommentOnCommentedRecordNotificationView(): void
    {
        $notification = $this->createCommentOnCommentedRecordNotification();

        $notificationView = $this->notificationViewFactory->create($notification);

        $this->assertNotificationViewContainsBasicNotificationInformation($notification, $notificationView);

        $this->assertStringContainsString($notification->getInitiator()->getUsername(), $notificationView->body->htmlDescription);
        $this->assertStringContainsString('написал(a) комментарий', $notificationView->body->htmlDescription);

        $this->assertStringContainsString($notification->getComment()->getText(), $notificationView->body->htmlContext);
        $this->assertStringContainsString('к записи', $notificationView->body->htmlContext);
        $this->assertStringContainsString($notification->getCommentedRecord()->getTitle(), $notificationView->body->htmlContext);
        $this->assertStringContainsString('которую вы ранее комментировали', $notificationView->body->htmlContext);
    }

    public function testFactoryCanCreateCommentOnRecordNotificationView(): void
    {
        $notification = $this->createCommentOnRecordNotification();

        $notificationView = $this->notificationViewFactory->create($notification);

        $this->assertNotificationViewContainsBasicNotificationInformation($notification, $notificationView);

        $this->assertStringContainsString($notification->getInitiator()->getUsername(), $notificationView->body->htmlDescription);
        $this->assertStringContainsString('написал(a) комментарий', $notificationView->body->htmlDescription);

        $this->assertStringContainsString($notification->getComment()->getText(), $notificationView->body->htmlContext);
        $this->assertStringContainsString('к записи', $notificationView->body->htmlContext);
        $this->assertStringContainsString($notification->getOwnerRecord()->getTitle(), $notificationView->body->htmlContext);
    }

    public function testFactoryCanCreatePositiveVoteOnCommentNotificationView(): void
    {
        $notification = $this->createPositiveVoteOnCommentNotification();

        $notificationView = $this->notificationViewFactory->create($notification);

        $this->assertNotificationViewContainsBasicNotificationInformation($notification, $notificationView);

        $this->assertStringContainsString($notification->getInitiator()->getUsername(), $notificationView->body->htmlDescription);
        $this->assertStringContainsString('оценил(а) комментарий', $notificationView->body->htmlDescription);

        $this->assertStringContainsString($notification->getOwnerComment()->getText(), $notificationView->body->htmlContext);
        $this->assertStringContainsString('к записи', $notificationView->body->htmlContext);
        $this->assertStringContainsString($notification->getOwnerComment()->getRecord()->getTitle(), $notificationView->body->htmlContext);
    }

    public function testFactoryCanCreatePositiveVoteOnRecordNotificationView(): void
    {
        $notification = $this->createPositiveVoteOnRecordNotification();

        $notificationView = $this->notificationViewFactory->create($notification);

        $this->assertNotificationViewContainsBasicNotificationInformation($notification, $notificationView);

        $this->assertStringContainsString($notification->getInitiator()->getUsername(), $notificationView->body->htmlDescription);
        $this->assertStringContainsString('оценил(a) запись', $notificationView->body->htmlDescription);

        $this->assertStringContainsString($notification->getOwnerRecord()->getTitle(), $notificationView->body->htmlContext);
    }

    public function testFactoryCanCreateViewForAggregatedCommentOnCommentedRecordNotification(): void
    {
        $sourceNotification = $this->createCommentOnCommentedRecordNotification();
        $aggregatedNotification = new AggregatedNotification(
            $sourceNotification,
            [
                $sourceNotification,
                $this->createCommentOnCommentedRecordNotification(),
            ]
        );

        $notificationView = $this->notificationViewFactory->create($aggregatedNotification);

        $this->assertNotificationViewContainsBasicAggregatedNotificationInformation($aggregatedNotification, $notificationView);

        /**
         * @var User $firstInitiator
         * @var User $secondInitiator
         */
        [$firstInitiator, $secondInitiator] = array_slice($aggregatedNotification->getUniqueInitiators(), 0, 2);

        $this->assertNotNull($notificationView->author);
        $this->assertStringContainsString($firstInitiator->getUsername(), $notificationView->body->htmlDescription);
        $this->assertStringContainsString($secondInitiator->getUsername(), $notificationView->body->htmlDescription);
        $this->assertStringContainsString('написали комментарии', $notificationView->body->htmlDescription);

        $this->assertStringContainsString('к записи', $notificationView->body->htmlContext);
        $this->assertStringContainsString($sourceNotification->getCommentedRecord()->getTitle(), $notificationView->body->htmlContext);
        $this->assertStringContainsString('которую вы ранее комментировали', $notificationView->body->htmlContext);
    }

    public function testFactoryCanCreateViewForAggregatedNewCommentOnCSelfRecordNotification(): void
    {
        $sourceNotification = $this->createCommentOnRecordNotification();
        $aggregatedNotification = new AggregatedNotification(
            $sourceNotification,
            [
                $sourceNotification,
                $this->createCommentOnRecordNotification(),
            ]
        );

        $notificationView = $this->notificationViewFactory->create($aggregatedNotification);

        $this->assertNotificationViewContainsBasicAggregatedNotificationInformation($aggregatedNotification, $notificationView);

        /**
         * @var User $firstInitiator
         * @var User $secondInitiator
         */
        [$firstInitiator, $secondInitiator] = array_slice($aggregatedNotification->getUniqueInitiators(), 0, 2);

        $this->assertNotNull($notificationView->author);
        $this->assertStringContainsString($firstInitiator->getUsername(), $notificationView->body->htmlDescription);
        $this->assertStringContainsString($secondInitiator->getUsername(), $notificationView->body->htmlDescription);
        $this->assertStringContainsString('написали комментарии', $notificationView->body->htmlDescription);

        $this->assertStringContainsString('к вашей записи', $notificationView->body->htmlContext);
        $this->assertStringContainsString($sourceNotification->getOwnerRecord()->getTitle(), $notificationView->body->htmlContext);
    }

    public function testFactoryCanCreateViewForAggregatedPositiveVoteOnCommentNotification(): void
    {
        $sourceNotification = $this->createPositiveVoteOnCommentNotification();
        $aggregatedNotification = new AggregatedNotification(
            $sourceNotification,
            [
                $sourceNotification,
                $this->createPositiveVoteOnCommentNotification(),
            ]
        );

        $notificationView = $this->notificationViewFactory->create($aggregatedNotification);

        $this->assertNotificationViewContainsBasicAggregatedNotificationInformation($aggregatedNotification, $notificationView);

        /**
         * @var User $firstInitiator
         * @var User $secondInitiator
         */
        [$firstInitiator, $secondInitiator] = array_slice($aggregatedNotification->getUniqueInitiators(), 0, 2);

        $this->assertNotNull($notificationView->author);
        $this->assertStringContainsString($firstInitiator->getUsername(), $notificationView->body->htmlDescription);
        $this->assertStringContainsString($secondInitiator->getUsername(), $notificationView->body->htmlDescription);
        $this->assertStringContainsString('оценили ваш комментарий', $notificationView->body->htmlDescription);

        $this->assertStringContainsString($sourceNotification->getOwnerComment()->getText(), $notificationView->body->htmlContext);
        $this->assertStringContainsString('к записи', $notificationView->body->htmlContext);
        $this->assertStringContainsString($sourceNotification->getOwnerComment()->getRecord()->getTitle(), $notificationView->body->htmlContext);
    }

    public function testFactoryCanCreateViewForAggregatedPositiveVoteOnRecordNotification(): void
    {
        $sourceNotification = $this->createPositiveVoteOnRecordNotification();
        $aggregatedNotification = new AggregatedNotification(
            $sourceNotification,
            [
                $sourceNotification,
                $this->createPositiveVoteOnRecordNotification(),
            ]
        );

        $notificationView = $this->notificationViewFactory->create($aggregatedNotification);

        $this->assertNotificationViewContainsBasicAggregatedNotificationInformation($aggregatedNotification, $notificationView);

        /**
         * @var User $firstInitiator
         * @var User $secondInitiator
         */
        [$firstInitiator, $secondInitiator] = array_slice($aggregatedNotification->getUniqueInitiators(), 0, 2);

        $this->assertNotNull($notificationView->author);
        $this->assertStringContainsString($firstInitiator->getUsername(), $notificationView->body->htmlDescription);
        $this->assertStringContainsString($secondInitiator->getUsername(), $notificationView->body->htmlDescription);
        $this->assertStringContainsString('оценили вашу запись', $notificationView->body->htmlDescription);

        $this->assertStringContainsString($sourceNotification->getOwnerRecord()->getTitle(), $notificationView->body->htmlContext);
    }

    public function testFactoryCanCreateViewForNewOwnershipRequestNotification(): void
    {
        $owner = $this->createUser('owner');
        $company = $this->createCompany('companyWithoutOwner', new AnonymousOwner());
        $ownershipRequestId = Uuid::uuid4();

        $ownershipRequest = new OwnershipRequest($ownershipRequestId, $owner, $company);

        $newOwnershipRequestNotification = new NewOwnershipRequestNotification($ownershipRequest);

        $notificationView = $this->notificationViewFactory->create($newOwnershipRequestNotification);

        $this->assertNotificationViewContainsBasicNotificationInformation($newOwnershipRequestNotification, $notificationView);
        $this->assertStringContainsString($owner->getName(), $notificationView->body->htmlDescription);
        $this->assertStringContainsString('создал запрос на подтверждение статуса владельца компании', $notificationView->body->htmlDescription);
        $this->assertStringContainsString($company->getName(), $notificationView->body->htmlDescription);
    }

    public function testFactoryCanCreateViewForNewSuggestedNewsNotification(): void
    {
        $author = $this->createUser('author');
        $suggestedNewsId = Uuid::uuid4();
        $suggestedNews = new SuggestedNews($suggestedNewsId, 'Заголовок', 'Текст', $author);

        $newSuggestedNewsNotification = new SuggestedNewsCreatedNotification($author, $suggestedNews);

        $notificationView = $this->notificationViewFactory->create($newSuggestedNewsNotification);

        $this->assertNotificationViewContainsBasicNotificationInformation($newSuggestedNewsNotification, $notificationView);
        $this->assertStringContainsString($author->getName(), $notificationView->body->htmlDescription);
        $this->assertStringContainsString('предложил(а) новость', $notificationView->body->htmlDescription);
    }

    public function testFactoryCanCreateViewForOwnershipRequestApprovedNotification(): void
    {
        $admin = $this->createUser('admin');
        $owner = $this->createUser('owner');
        $company = $this->createCompany('owner company', $owner);

        $ownershipRequestApprovedNotification = new OwnershipRequestApprovedNotification($admin, $company);

        $notificationView = $this->notificationViewFactory->create($ownershipRequestApprovedNotification);

        $this->assertNotificationViewContainsBasicNotificationInformation($ownershipRequestApprovedNotification, $notificationView);
        $this->assertStringContainsString('Ваша заявка на владение компанией', $notificationView->body->htmlDescription);
        $this->assertStringContainsString($company->getName(), $notificationView->body->htmlDescription);
        $this->assertStringContainsString('Теперь вы можете редактировать профиль и публиковать новости от имени компании', $notificationView->body->htmlDescription);
    }

    public function testFactoryCanCreateViewForCompanyArticleCreatedNotification(): void
    {
        $admin = $this->createUser('admin');
        $owner = $this->createUser('owner');
        $company = $this->createCompany('owner company', $owner);
        $companyArticle = $this->createCompanyArticle('prewiew', $admin, $company);

        $companyArticleCreatedNotification = new CompanyArticleCreatedNotification($admin, $companyArticle);

        $notificationView = $this->notificationViewFactory->create($companyArticleCreatedNotification);

        $this->assertNotificationViewContainsBasicNotificationInformation($companyArticleCreatedNotification, $notificationView);
        $this->assertStringContainsString('У компании', $notificationView->body->htmlDescription);
        $this->assertStringContainsString($company->getName(), $notificationView->body->htmlDescription);
        $this->assertStringContainsString('вышла новая публикация.', $notificationView->body->htmlDescription);
    }

    public function testFactoryCanCreateViewForCompanyCreatedNotification(): void
    {
        $owner = $this->createUser('owner');
        $company = $this->createCompany('owner company', $owner);

        $companyCreatedNotification = new CompanyCreatedNotification($owner, $company);

        $notificationView = $this->notificationViewFactory->create($companyCreatedNotification);

        $this->assertStringContainsString('Была создана новая компания - ', $notificationView->body->htmlDescription);
        $this->assertStringContainsString($company->getName(), $notificationView->body->htmlDescription);
        $this->assertStringContainsString('.', $notificationView->body->htmlDescription);
    }

    public function testFactoryCanCreateArticleCreatedNotificationView(): void
    {
        $initiator = $this->createUser('notification-initiator');
        $article = $this->createUserArticle($initiator);

        $expectedHtmlDescription = sprintf(
            'Пользователь "<a href="/users/profile/%d/">%s</a>" опубликовал(а) новую запись: <a href="/articles/view/%d/">"%s"</a>.',
            $initiator->getId(),
            $initiator->getUsername(),
            $article->getId(),
            $article->getTitle(),
        );

        $notification = new ArticleCreatedNotification($initiator, $article);

        $notificationView = $this->notificationViewFactory->create($notification);

        $this->assertNotificationViewContainsBasicNotificationInformation($notification, $notificationView);
        $this->assertEquals($expectedHtmlDescription, $notificationView->body->htmlDescription);
    }

    public function testFactoryCanCreateUserTidingsCreatedNotificationView(): void
    {
        $initiator = $this->createUser('notification-initiator');
        $tidings = $this->createUserTidings($initiator);

        $expectedHtmlDescription = sprintf(
            'Пользователь "<a href="/users/profile/%d/">%s</a>" опубликовал(а) весть с водоема: <a href="/tidings/view/%d/">"%s"</a>.',
            $initiator->getId(),
            $initiator->getUsername(),
            $tidings->getId(),
            $tidings->getTitle(),
        );

        $notification = new TidingsCreatedNotification($initiator, $tidings);

        $notificationView = $this->notificationViewFactory->create($notification);

        $this->assertNotificationViewContainsBasicNotificationInformation($notification, $notificationView);
        $this->assertEquals($expectedHtmlDescription, $notificationView->body->htmlDescription);
    }

    public function testFactoryCanCreateUserGalleryCreatedNotificationView(): void
    {
        $initiator = $this->createUser('notification-initiator');
        $gallery = $this->createUserGallery($initiator);

        $expectedHtmlDescription = sprintf(
            'Пользователь "<a href="/users/profile/%d/">%s</a>" опубликовал(а) фото: <a href="/gallery/view/%d/">"%s"</a>.',
            $initiator->getId(),
            $initiator->getUsername(),
            $gallery->getId(),
            $gallery->getTitle(),
        );

        $notification = new GalleryCreatedNotification($initiator, $gallery);

        $notificationView = $this->notificationViewFactory->create($notification);

        $this->assertNotificationViewContainsBasicNotificationInformation($notification, $notificationView);
        $this->assertEquals($expectedHtmlDescription, $notificationView->body->htmlDescription);
    }

    public function testFactoryCanCreateUserVideoCreatedNotificationView(): void
    {
        $initiator = $this->createUser('notification-initiator');
        $video = $this->createUserVideo($initiator);

        $expectedHtmlDescription = sprintf(
            'Пользователь "<a href="/users/profile/%d/">%s</a>" опубликовал(а) видео: <a href="/video/view/%d/">"%s"</a>.',
            $initiator->getId(),
            $initiator->getUsername(),
            $video->getId(),
            $video->getTitle(),
        );

        $notification = new VideoCreatedNotification($initiator, $video);

        $notificationView = $this->notificationViewFactory->create($notification);

        $this->assertNotificationViewContainsBasicNotificationInformation($notification, $notificationView);
        $this->assertEquals($expectedHtmlDescription, $notificationView->body->htmlDescription);
    }

    public function testFactoryCanCreateUserMapCreatedNotificationView(): void
    {
        $initiator = $this->createUser('notification-initiator');
        $map = $this->createUserMap($initiator);

        $expectedHtmlDescription = sprintf(
            'Пользователь "<a href="/users/profile/%d/">%s</a>" опубликовал(а) точку на карте: <a href="/maps/view/%d/">"%s"</a>.',
            $initiator->getId(),
            $initiator->getUsername(),
            $map->getId(),
            $map->getTitle(),
        );

        $notification = new MapCreatedNotification($initiator, $map);

        $notificationView = $this->notificationViewFactory->create($notification);

        $this->assertNotificationViewContainsBasicNotificationInformation($notification, $notificationView);
        $this->assertEquals($expectedHtmlDescription, $notificationView->body->htmlDescription);
    }

    public function testFactoryCanCreateNotificationViewWithLogoSrc(): void
    {
        $authorMock = $this->createMock(User::class);
        $authorMock->method('getAvatar')->willReturn(new Avatar(new Image('/image.png')));
        $authorMock->method('getId')->willReturn(1);

        $article = $this->createUserArticle($authorMock);

        $notification = new ArticleCreatedNotification($authorMock, $article);
        $notificationView = $this->notificationViewFactory->create($notification);

        $expectedLogoSrc = sprintf(
            '%s%s/image__rsf-%s-%s.png',
            getenv('IMAGE_STORAGE_URL'),
            getenv('IMAGE_STORAGE_ID'),
            NotificationLogoPathGenerator::LOGO_SIZE,
            NotificationLogoPathGenerator::LOGO_SIZE,
        );

        $this->assertStringStartsWith($expectedLogoSrc, $notificationView->logoPath);
    }

    public function testFactoryCanCreateNotificationViewWithCompanyLogoSrc(): void
    {
        $initiator = $this->createUser('initiator');

        $companyMock = $this->createMock(Company::class);
        $companyMock->method('getLogoImage')->willReturn(new LogoImage(new Image('/image.png')));

        $notification = new CompanyCreatedNotification($initiator, $companyMock);
        $notificationView = $this->notificationViewFactory->create($notification);

        $expectedLogoSrc = sprintf(
            '%s%s/image__rsf-%s-%s.png',
            getenv('IMAGE_STORAGE_URL'),
            getenv('IMAGE_STORAGE_ID'),
            NotificationLogoPathGenerator::LOGO_SIZE,
            NotificationLogoPathGenerator::LOGO_SIZE,
        );

        $this->assertStringStartsWith($expectedLogoSrc, $notificationView->logoPath);
    }

    private function assertNotificationViewContainsBasicNotificationInformation(Notification $notification, NotificationView $actualView): void
    {
        $this->assertTrue($notification->getCategory()->equals($actualView->category));
        $this->assertEquals($notification->getCreatedAt()->format('Y-m-d H:i:s'), $actualView->createdAt->format('Y-m-d H:i:s'));
        $this->assertEquals($notification->isRead(), $actualView->isRead);
        $this->assertEquals(!$notification instanceof ForumNotification, $actualView->isInternal);
        $this->assertEquals(1, $actualView->numericalRepresentation);
    }

    private function assertNotificationViewContainsBasicAggregatedNotificationInformation(AggregatedNotification $aggregatedNotification, NotificationView $actualView): void
    {
        $this->assertTrue($aggregatedNotification->getCategory()->equals($actualView->category));
        $this->assertEquals($aggregatedNotification->getCreatedAt()->format('Y-m-d H:i:s'), $actualView->createdAt->format('Y-m-d H:i:s'));
        $this->assertEquals($aggregatedNotification->isRead(), $actualView->isRead);
        $this->assertTrue($actualView->isInternal);
        $this->assertEquals(count($aggregatedNotification->getNotifications()), $actualView->numericalRepresentation);
    }

    private function createForumNotification(): ForumNotification
    {
        return new ForumNotification(
            1,
            'some message',
            NotificationCategory::mention(),
            $this->createUser('initiator')
        );
    }

    private function createCommentOnCommentedRecordNotification(): CommentOnCommentedRecordNotification
    {
        $record = $this->createRecord('record title', $this->createUser('record owner'));
        $comment = $this->createComment('comment text', $record, $this->createUser('commenter'));

        return new CommentOnCommentedRecordNotification(
            $record,
            $comment
        );
    }

    private function createCommentOnRecordNotification(): CommentOnRecordNotification
    {
        $notificationOwner = $this->createUser('owner');
        $record = $this->createRecord('record title', $notificationOwner);
        $comment = $this->createComment('comment text', $record, $this->createUser('commenter'));

        return new CommentOnRecordNotification(
            $record,
            $comment
        );
    }

    private function createPositiveVoteOnCommentNotification(): PositiveVoteOnCommentNotification
    {
        $notificationOwner = $this->createUser('owner');
        $record = $this->createRecord('record title', $notificationOwner);
        $comment = $this->createComment('comment text', $record, $notificationOwner);
        $vote = $this->createVote($this->createUser('voter'));

        return new PositiveVoteOnCommentNotification(
            $comment,
            $vote
        );
    }

    private function createPositiveVoteOnRecordNotification(): PositiveVoteOnRecordNotification
    {
        $notificationOwner = $this->createUser('owner');
        $record = $this->createRecord('record title', $notificationOwner);
        $vote = $this->createVote($this->createUser('voter'));

        return new PositiveVoteOnRecordNotification(
            $record,
            $vote
        );
    }

    private function createRecord(string $titleAndDescription, AuthorInterface $author): Record
    {
        $stub = $this->createMock(Map::class);
        $stub
            ->method('getAuthor')
            ->willReturn($author);
        $stub
            ->method('getTitle')
            ->willReturn($titleAndDescription);
        $stub
            ->method('getDescription')
            ->willReturn($titleAndDescription);
        $stub
            ->method('getVotableId')
            ->willReturn(new VotableIdentifier('1', 'votable'));
        $stub
            ->method('getComments')
            ->willReturn(new CommentCollection());
        $stub
            ->method('getCommentsWithAnswers')
            ->willReturn(new CommentCollection());

        return $stub;
    }

    private function createComment(string $text, Record $record, AuthorInterface $author): Comment
    {
        $stub = $this->createMock(Comment::class);
        $stub
            ->method('getText')
            ->willReturn($text);
        $stub
            ->method('getRecord')
            ->willReturn($record);
        $stub
            ->method('getAuthor')
            ->willReturn($author);
        $stub
            ->method('getCreatedAt')
            ->willReturn(Carbon::now());
        $stub
            ->method('getVotableId')
            ->willReturn(new VotableIdentifier('1', 'votable'));

        return $stub;
    }

    private function createUser(string $username): User
    {
        $stub = $this->createMock(User::class);
        $stub
            ->method('getId')
            ->willReturn(1);
        $stub
            ->method('getUsername')
            ->willReturn($username);

        return $stub;
    }

    private function createVote(VoterInterface $voter): Vote
    {
        $stub = $this->createMock(Vote::class);
        $stub
            ->method('belongsToVotable')
            ->willReturn(true);
        $stub
            ->method('isPositive')
            ->willReturn(true);

        $stub
            ->method('getVoter')
            ->willReturn($voter);

        return $stub;
    }

    private function createCompany(string $companyName, OwnerInterface $owner): Company
    {
        $company = $this->createMock(Company::class);

        $company->method('getId')->willReturn(Uuid::uuid4());
        $company->method('getName')->willReturn($companyName);
        $company->method('getOwner')->willReturn($owner);

        return $company;
    }

    private function createCompanyArticle(string $companyArticlePrewiew, ?User $owner = null, ?Company $company = null): CompanyArticle
    {
        $companyArticle = $this->createMock(CompanyArticle::class);

        $companyArticle->method('getId')->willReturn(1);
        $companyArticle->method('getPreview')->willReturn($companyArticlePrewiew);
        $companyArticle->method('getOwner')->willReturn($owner);
        $companyArticle->method('getCompanyAuthor')->willReturn($company);

        return $companyArticle;
    }

    private function createUserArticle(User $author): Article
    {
        $userArticle = $this->createMock(Article::class);

        $userArticle->method('getId')->willReturn(5);
        $userArticle->method('getTitle')->willReturn('article-title');
        $userArticle->method('getAuthor')->willReturn($author);
        $userArticle->method('getVotableId')->willReturn(new VotableIdentifier('1', 'votable'));
        $userArticle->method('getComments')->willReturn(new CommentCollection());
        $userArticle->method('getCommentsWithAnswers')->willReturn(new CommentCollection());
        $userArticle->method('getRecordSemanticLinks')->willReturn(new RecordSemanticLinkCollection());
        $userArticle->method('getImages')->willReturn(new ImageCollection());

        return $userArticle;
    }

    private function createUserTidings(User $author): Tidings
    {
        $userTidings = $this->createMock(Tidings::class);

        $userTidings->method('getId')->willReturn(5);
        $userTidings->method('getTitle')->willReturn('tidings-title');
        $userTidings->method('getAuthor')->willReturn($author);
        $userTidings->method('getVotableId')->willReturn(new VotableIdentifier('1', 'votable'));
        $userTidings->method('getComments')->willReturn(new CommentCollection());
        $userTidings->method('getCommentsWithAnswers')->willReturn(new CommentCollection());
        $userTidings->method('getRecordSemanticLinks')->willReturn(new RecordSemanticLinkCollection());
        $userTidings->method('getImages')->willReturn(new ImageCollection());
        $userTidings->method('getVideoUrls')->willReturn(new YoutubeVideoUrlCollection());

        return $userTidings;
    }

    private function createUserGallery(User $author): Gallery
    {
        $userGallery = $this->createMock(Gallery::class);

        $userGallery->method('getId')->willReturn(5);
        $userGallery->method('getTitle')->willReturn('tidings-title');
        $userGallery->method('getAuthor')->willReturn($author);
        $userGallery->method('getVotableId')->willReturn(new VotableIdentifier('1', 'votable'));
        $userGallery->method('getComments')->willReturn(new CommentCollection());
        $userGallery->method('getCommentsWithAnswers')->willReturn(new CommentCollection());
        $userGallery->method('getCreatedAt')->willReturn(new DateTime());

        return $userGallery;
    }

    private function createUserVideo(User $author): Video
    {
        $userVideo = $this->createMock(Video::class);

        $userVideo->method('getId')->willReturn(5);
        $userVideo->method('getTitle')->willReturn('tidings-title');
        $userVideo->method('getAuthor')->willReturn($author);
        $userVideo->method('getVotableId')->willReturn(new VotableIdentifier('1', 'votable'));
        $userVideo->method('getComments')->willReturn(new CommentCollection());
        $userVideo->method('getCommentsWithAnswers')->willReturn(new CommentCollection());

        return $userVideo;
    }

    private function createUserMap(User $author): Map
    {
        $userMap = $this->createMock(Map::class);

        $userMap->method('getId')->willReturn(5);
        $userMap->method('getTitle')->willReturn('tidings-title');
        $userMap->method('getAuthor')->willReturn($author);
        $userMap->method('getVotableId')->willReturn(new VotableIdentifier('1', 'votable'));
        $userMap->method('getComments')->willReturn(new CommentCollection());
        $userMap->method('getCommentsWithAnswers')->willReturn(new CommentCollection());

        return $userMap;
    }
}
