<?php

namespace Tests\DataFixtures\ORM\User;

use App\Domain\Category\Entity\Category;
use App\Domain\Record\Article\Entity\Article;
use App\Domain\User\Entity\Notification\CommentOnRecordNotification;
use App\Domain\User\Entity\Notification\ForumNotification;
use App\Domain\User\Entity\Notification\ValueObject\NotificationCategory;
use App\Domain\User\Entity\User;
use App\Domain\User\Entity\ValueObject\LastVisit;
use App\Domain\User\Entity\ValueObject\PasswordHashingOptions;
use App\Module\Author\AuthorInterface;
use App\Util\ImageStorage\Collection\ImageCollection;
use DateTime;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Faker\Generator;
use Tests\DataFixtures\Helper\DefaultUserPasswordGenerator;
use Tests\DataFixtures\ORM\LoadCategories;
use Ramsey\Uuid\Uuid;

class LoadUserWithUnreadNotifications extends UserFixture implements DependentFixtureInterface
{
    public const REFERENCE_NAME = 'user-with-unread-notifications';

    private Generator $generator;
    private DefaultUserPasswordGenerator $passwordGenerator;

    public function __construct(Generator $generator, DefaultUserPasswordGenerator $passwordGenerator)
    {
        $this->generator = $generator;
        $this->passwordGenerator = $passwordGenerator;
    }

    public function load(ObjectManager $manager): void
    {
        $user = $this->createUser();

        $notificationInitiator = $this->getReference(LoadTestUser::USER_TEST);
        assert($notificationInitiator instanceof User);

        $secondNotificationInitiator = $this->getReference(LoadUserWithAvatar::REFERENCE_NAME);
        assert($secondNotificationInitiator instanceof User);

        $this->notifyUserWithForumNotifications($user, $notificationInitiator);

        $notificationInitiators = [$notificationInitiator, $secondNotificationInitiator];
        $this->notifyUserWithCommentOnRecordNotifications($user, $notificationInitiators, $manager);

        $this->addReference(self::REFERENCE_NAME, $user);
        $manager->persist($user);
        $manager->flush();
    }

    private function createUser(): User
    {
        $lastVisit = new LastVisit($this->generator->ipv4, new DateTime());

        $user = new User(
            'user-with-unread-notifications',
            'user-with-unread-notifications@fishingsib.loc',
            $this->passwordGenerator->generate(),
            new PasswordHashingOptions(),
            $lastVisit
        );
        $user
            ->confirmEmail()
            ->setForumUserId(self::getForumUserId());

        return $user;
    }

    private function notifyUserWithForumNotifications(User $user, User $notificationInitiator): void
    {
        $notification = $this->createForumNotification(
            1,
            'Notification from forum',
            NotificationCategory::mention(),
            $notificationInitiator,
        );
        $user->notify($notification->withOwner($user));

        $notification = $this->createForumNotification(
            2,
            'Some other notification from forum',
            NotificationCategory::like(),
            $notificationInitiator,
        );
        $user->notify($notification->withOwner($user));
    }

    /**
     * @param User[] $notificationInitiators
     *
     * @throws \Exception
     */
    private function notifyUserWithCommentOnRecordNotifications(User $user, array $notificationInitiators, ObjectManager $manager): void
    {
        $category = $this->getReference(LoadCategories::getRandReferenceNameForRootCategory(
            LoadCategories::ROOT_ARTICLES
        ));
        assert($category instanceof Category);

        $record = $this->createRecord($user, $category);
        $manager->persist($record);

        foreach ($notificationInitiators as $notificationInitiator) {
            assert($notificationInitiator instanceof User);
            $comment = $record->addComment(
                Uuid::uuid4(),
                $this->generator->regexify('[A-Za-z0-9]{20}'),
                'Отличный комментарий, чтобы его написать.',
                $notificationInitiator
            );

            $notification = new CommentOnRecordNotification($record, $comment);
            $user->notify($notification->withOwner($user));
        }
    }

    private function createForumNotification(string $id, string $message, NotificationCategory $notificationCategory, AuthorInterface $author): ForumNotification
    {
        return new ForumNotification(
            $id,
            $message,
            $notificationCategory,
            $author,
        );
    }

    private function createRecord(AuthorInterface $user, Category $category): Article
    {
        return new Article(
            'user-with-unread-notifications article',
            'user-with-unread-notifications article data',
            $user,
            $category,
            false,
            new ImageCollection(),
        );
    }

    /**
     * @return string[]
     */
    public function getDependencies(): array
    {
        return [
            LoadTestUser::class,
            LoadUserWithAvatar::class,
            LoadCategories::class,
        ];
    }
}
