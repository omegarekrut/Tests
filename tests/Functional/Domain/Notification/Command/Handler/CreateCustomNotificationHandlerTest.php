<?php

namespace Tests\Functional\Domain\Notification\Command\Handler;

use App\Domain\Notification\Command\CreateCustomNotificationCommand;
use App\Domain\Notification\Command\Handler\CreateCustomNotificationHandler;
use App\Domain\Notification\Entity\CustomNotification;
use App\Domain\Notification\Repository\CustomNotificationRepository;
use App\Domain\User\Command\Notification\Custom\NotifyNotBannedUsersAboutCustomNotificationCommand;
use App\Domain\User\Entity\User;
use Ramsey\Uuid\Uuid;
use Tests\DataFixtures\ORM\User\LoadAdminUser;
use Tests\Functional\TestCase;
use Tests\Unit\Mock\CommandBusMock;

/**
 * @group notification
 */
final class CreateCustomNotificationHandlerTest extends TestCase
{
    public function testNotificationIsCteated(): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadAdminUser::class,
        ])->getReferenceRepository();

        $existsAdminUser = $referenceRepository->getReference(LoadAdminUser::REFERENCE_NAME);
        assert($existsAdminUser instanceof User);
        $notificationRepository = $this->getContainer()->get(CustomNotificationRepository::class);

        $command = new CreateCustomNotificationCommand(Uuid::uuid4(), $existsAdminUser);
        $command->title = 'new notification title';
        $command->message = 'new notification message';

        $commandBus = new CommandBusMock();
        $handler = new CreateCustomNotificationHandler($notificationRepository, $commandBus);
        $handler->handle($command);

        $notificationList = $notificationRepository->findAllByTitle($command->title);

        $this->assertCount(1, $notificationList);

        $actualNotification = current($notificationList);
        assert($actualNotification instanceof CustomNotification);
        $lastHandledCommand = $commandBus->getLastHandledCommand();
        assert($lastHandledCommand instanceof NotifyNotBannedUsersAboutCustomNotificationCommand);

        $this->assertEquals($command->title, $actualNotification->getTitle());
        $this->assertEquals($command->message, $actualNotification->getMessage());

        $this->assertInstanceOf(NotifyNotBannedUsersAboutCustomNotificationCommand::class, $lastHandledCommand);
        $this->assertEquals((string) $actualNotification->getId(), $lastHandledCommand->notificationId);
    }
}
