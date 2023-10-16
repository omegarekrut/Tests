<?php

namespace Tests\DataFixtures\ORM\User;

use App\Domain\User\Entity\Notification\ForumNotification;
use App\Domain\User\Entity\Notification\ValueObject\NotificationCategory;
use App\Domain\User\Entity\User;
use App\Domain\User\Entity\ValueObject\LastVisit;
use App\Domain\User\Entity\ValueObject\PasswordHashingOptions;
use Carbon\Carbon;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Tests\DataFixtures\Helper\DefaultUserPasswordGenerator;

class LoadUserWithNotifications extends Fixture implements DependentFixtureInterface
{
    public const REFERENCE_NAME = 'user-with-notifications';

    private \Faker\Generator $generator;
    private DefaultUserPasswordGenerator $passwordGenerator;

    public function __construct(\Faker\Generator $generator, DefaultUserPasswordGenerator $passwordGenerator)
    {
        $this->generator = $generator;
        $this->passwordGenerator = $passwordGenerator;
    }

    public function load(ObjectManager $manager): void
    {
        $user = new User(
            'user-with-notifications',
            'user-with-notifications@fishingsib.loc',
            $this->passwordGenerator->generate(),
            new PasswordHashingOptions(),
            new LastVisit($this->generator->ipv4, Carbon::now())
        );

        $user
            ->confirmEmail()
            ->setForumUserId(9);

        $this->createNotifications($user);

        $this->addReference(self::REFERENCE_NAME, $user);
        $manager->persist($user);
        $manager->flush();
    }

    private function createNotifications(User $user): void
    {
        /** @var User $notificationInitiator */
        $notificationInitiator = $this->getReference(LoadTestUser::USER_TEST);

        try {
            $notificationId = 1;
            $notification = $this->createMentionForumNotification($notificationId, $notificationInitiator);
            $user->notify($notification->withOwner($user));

            $notificationId++;
            Carbon::setTestNow(Carbon::now()->addSeconds($notificationId));
            $notification = $this->createLikeForumNotification($notificationId, $notificationInitiator);
            $user->notify($notification->withOwner($user));

            $user->readAllUnreadNotifications();

            $notificationId++;
            Carbon::setTestNow(Carbon::now()->addSeconds($notificationId));
            $notification = $this->createLikeForumNotification($notificationId, $notificationInitiator);
            $user->notify($notification->withOwner($user));

            $notificationId++;
            Carbon::setTestNow(Carbon::now()->addSeconds($notificationId));
            $notification = $this->createLikeForumNotification($notificationId, $notificationInitiator);
            $user->notify($notification->withOwner($user));
        } finally {
            Carbon::setTestNow();
        }
    }

    private function createMentionForumNotification(int $id, User $initiator): ForumNotification
    {
        return new ForumNotification(
            $id,
            '@you hello',
            NotificationCategory::mention(),
            $initiator,
        );
    }

    private function createLikeForumNotification(int $id, User $initiator): ForumNotification
    {
        return new ForumNotification(
            $id,
            'Your post was liked',
            NotificationCategory::like(),
            $initiator,
        );
    }

    /**
     * @return string[]
     */
    public function getDependencies(): array
    {
        return [
            LoadTestUser::class,
        ];
    }
}
