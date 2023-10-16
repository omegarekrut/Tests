<?php

namespace Tests\Functional\Domain\User\Command\Notification\Handler;

use App\Domain\User\Command\Notification\DeleteOldNotificationsCommand;
use App\Domain\User\Entity\User;
use Doctrine\Common\DataFixtures\ReferenceRepository;
use Tests\DataFixtures\ORM\User\LoadUserWithNotifications;
use Tests\Functional\TestCase;

class DeleteOldNotificationsHandlerTest extends TestCase
{
    /** @var ReferenceRepository  */
    private $referenceRepository;
    /** @var User */
    private $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->referenceRepository = $this->loadFixtures([
            LoadUserWithNotifications::class,
        ])->getReferenceRepository();

        $this->user = $this->referenceRepository->getReference(
            LoadUserWithNotifications::REFERENCE_NAME
        );
    }

    protected function tearDown(): void
    {
        unset(
            $this->user,
            $this->referenceRepository
        );

        parent::tearDown();
    }

    public function testShouldDeleteOldNotifications(): void
    {
        $savedReadNotificationsCount = 1;
        $savedUnreadNotificationsCount = 1;
        $readNotifications = $this->user->getReadNotifications()->count();
        $unreadNotifications = $this->user->getUnreadNotifications()->count();

        $this->assertGreaterThan(1, $readNotifications);
        $this->assertGreaterThan(1, $unreadNotifications);

        $expectedReadNotification = $this->user->getReadNotifications()->first();
        $expectedUnreadNotification = $this->user->getUnreadNotifications()->first();

        $command = new DeleteOldNotificationsCommand(
            $this->user->getId(),
            $savedReadNotificationsCount,
            $savedUnreadNotificationsCount
        );

        $this->getCommandBus()->handle($command);

        $this->assertSame($savedUnreadNotificationsCount, $this->user->getUnreadNotifications()->count());
        $this->assertEquals($expectedUnreadNotification, $this->user->getUnreadNotifications()->first());
        $this->assertSame($savedReadNotificationsCount, $this->user->getReadNotifications()->count());
        $this->assertEquals($expectedReadNotification, $this->user->getReadNotifications()->first());
    }
}
