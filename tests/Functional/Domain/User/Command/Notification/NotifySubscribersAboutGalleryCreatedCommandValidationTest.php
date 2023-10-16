<?php

namespace Tests\Functional\Domain\User\Command\Notification;

use App\Domain\User\Command\Notification\NotifySubscribersAboutGalleryCreatedCommand;
use Tests\DataFixtures\ORM\Record\LoadGallery;
use Tests\Functional\ValidationTestCase;

/**
 * @group notification
 */
class NotifySubscribersAboutGalleryCreatedCommandValidationTest extends ValidationTestCase
{
    public function testCommandValidationFailedWithIncorrectGalleryId(): void
    {
        $invalidCommand = new NotifySubscribersAboutGalleryCreatedCommand(0);

        $this->getValidator()->validate($invalidCommand);

        $this->assertFieldInvalid('galleryId', 'Фото не найдено.');
    }

    public function testCommandValidationPassedWithCorrectGalleryId(): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadGallery::class,
        ])->getReferenceRepository();

        $correctGalleryId = $referenceRepository->getReference(LoadGallery::getRandReferenceName())->getId();

        $validCommand = new NotifySubscribersAboutGalleryCreatedCommand($correctGalleryId);

        $this->getValidator()->validate($validCommand);

        $this->assertEmpty($this->getValidator()->getLastErrors());
    }
}
