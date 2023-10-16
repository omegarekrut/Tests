<?php

namespace Tests\Unit\Domain\Notification\Service;

use App\Domain\Notification\Service\NotificatorService;
use App\Domain\User\Entity\Notification\ForumNotification;
use App\Domain\User\Entity\Notification\Notification;
use App\Domain\User\Entity\Notification\ValueObject\NotificationCategory;
use App\Domain\User\Entity\User;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Tests\Unit\TestCase;

class NotificatorServiceTest extends TestCase
{
    private const BATCH_SIZE = 500;

    /**
     * @dataProvider numberOfUsersDataProvider
     */
    public function testNotifyUsers(int $numberOfUsers): void
    {
        $notification = $this->createNotification();
        $users = [];

        for ($i = 0; $i < $numberOfUsers; $i++) {
            $users[] = $this->createUserMockWithExpectedNotification($notification);
        }

        $userRepository = $this->createEntityManagerMock($users);

        $notificator = new NotificatorService($userRepository);
        $notificator->notifyUsers($users, $notification);
    }

    /**
     * @return int[]
     */
    public function numberOfUsersDataProvider(): iterable
    {
        yield [0];

        yield [1];

        yield [self::BATCH_SIZE - 1];

        yield [self::BATCH_SIZE];

        yield [self::BATCH_SIZE + 1];
    }

    /**
     * @param User[] $users
     */
    private function createEntityManagerMock(array $users): EntityManagerInterface
    {
        $numberOfBatch = (int) (count($users) / self::BATCH_SIZE) + (int) (count($users) % self::BATCH_SIZE !== 0);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager
            ->expects($this->exactly($numberOfBatch))
            ->method('flush');

        $entityManager
            ->expects($this->exactly($numberOfBatch * 2))
            ->method('getConnection')
            ->willReturn($this->createConnectionMock($numberOfBatch));

        return $entityManager;
    }

    private function createConnectionMock(int $numberOfBatch): Connection
    {
        $connection = $this->createMock(Connection::class);

        $connection
            ->expects($this->exactly($numberOfBatch))
            ->method('commit')
            ->willReturn(true);

        $connection
            ->expects($this->exactly($numberOfBatch))
            ->method('beginTransaction')
            ->willReturn(true);

        return $connection;
    }

    private function createNotification(): Notification
    {
        $initiator = $this->createMock(User::class);

        return new ForumNotification(1, 'hi', NotificationCategory::mention(), $initiator);
    }

    private function createUserMockWithExpectedNotification(Notification $notification): User
    {
        $user = $this->createMock(User::class);
        $expectedNotification = $notification->withOwner($user);

        $user
            ->expects($this->once())
            ->method('notify')
            ->with($expectedNotification);

        return $user;
    }
}
