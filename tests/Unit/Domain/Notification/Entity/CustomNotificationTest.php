<?php

namespace Tests\Unit\Domain\Notification\Entity;

use App\Domain\Notification\Command\UpdateCustomNotificationCommand;
use App\Domain\Notification\Entity\CustomNotification;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Tests\Unit\TestCase;

class CustomNotificationTest extends TestCase
{
    /**
     * @throws \Exception
     */
    public function testCustomNotificationCanBeCreated(): void
    {
        $notificationUuid = Uuid::uuid4();
        $notificationMessage = 'message';
        $notificationTitle = 'title';

        $notification = $this->createCustomNotification($notificationUuid, $notificationMessage, $notificationTitle);

        $this->assertInstanceOf(CustomNotification::class, $notification);
        $this->assertEquals($notificationUuid, $notification->getId());
        $this->assertEquals($notificationMessage, $notification->getMessage());
        $this->assertEquals($notificationTitle, $notification->getTitle());
    }

    /**
     * @throws \Exception
     */
    public function testRewriteTitleAndMessageWhenCustomNotificationUpdate(): void
    {
        $notification = $this->createCustomNotification(Uuid::uuid4());
        $rewriteTitle = 'rewrite title';
        $rewriteMessage = 'rewrite message';
        $updateNotification = new UpdateCustomNotificationCommand($notification);
        $updateNotification->title = $rewriteTitle;
        $updateNotification->message = $rewriteMessage;

        $notification->rewriteFromDTO($updateNotification);

        $this->assertEquals($rewriteTitle, $notification->getTitle());
        $this->assertEquals($rewriteMessage, $notification->getMessage());
    }

    private function createCustomNotification(
        UuidInterface $uuid,
        string $message = 'message',
        string $title = 'title'
    ): CustomNotification {
        return new CustomNotification($uuid, $message, $title);
    }
}
