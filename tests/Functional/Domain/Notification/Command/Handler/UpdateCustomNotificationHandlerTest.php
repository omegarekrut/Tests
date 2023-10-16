<?php

namespace Tests\Functional\Domain\Notification\Command\Handler;

use App\Domain\Notification\Command\UpdateCustomNotificationCommand;
use App\Domain\Notification\Entity\CustomNotification;
use Tests\DataFixtures\ORM\Notification\LoadCustomNotification;
use Tests\Functional\TestCase;

/**
 * @group notification
 */
class UpdateCustomNotificationHandlerTest extends TestCase
{
    public function testNotificationIsUpdated(): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadCustomNotification::class,
        ])->getReferenceRepository();

        /** @var CustomNotification $customNotification */
        $customNotification = $referenceRepository->getReference(LoadCustomNotification::REFERENCE_NAME);

        $command = new UpdateCustomNotificationCommand($customNotification);
        $command->title = 'updated '.$customNotification->getTitle();
        $command->message = 'updated '.$customNotification->getMessage();

        $this->getCommandBus()->handle($command);

        $this->assertEquals($command->title, $customNotification->getTitle());
        $this->assertEquals($command->message, $customNotification->getMessage());
    }
}
