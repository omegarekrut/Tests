<?php

namespace Tests\Functional\Domain\User\Command\Notification;

use App\Domain\User\Command\Notification\NotifySubscribersAboutVideoCreatedCommand;
use Tests\DataFixtures\ORM\Record\LoadVideos;
use Tests\Functional\ValidationTestCase;

/**
 * @group notification
 */
class NotifySubscribersAboutVideoCreatedCommandValidationTest extends ValidationTestCase
{
    public function testCommandValidationFailedWithIncorrectVideoId(): void
    {
        $invalidCommand = new NotifySubscribersAboutVideoCreatedCommand(0);

        $this->getValidator()->validate($invalidCommand);

        $this->assertFieldInvalid('videoId', 'Видео не найдено.');
    }

    public function testCommandValidationPassedWithCorrectVideoId(): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadVideos::class,
        ])->getReferenceRepository();

        $correctVideoId = $referenceRepository->getReference(LoadVideos::getRandReferenceName())->getId();

        $validCommand = new NotifySubscribersAboutVideoCreatedCommand($correctVideoId);

        $this->getValidator()->validate($validCommand);

        $this->assertEmpty($this->getValidator()->getLastErrors());
    }
}
