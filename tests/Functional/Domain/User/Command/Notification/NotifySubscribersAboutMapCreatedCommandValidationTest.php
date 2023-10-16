<?php

namespace Tests\Functional\Domain\User\Command\Notification;

use App\Domain\User\Command\Notification\NotifySubscribersAboutMapCreatedCommand;
use Tests\DataFixtures\ORM\Record\LoadMaps;
use Tests\Functional\ValidationTestCase;

/**
 * @group notification
 */
class NotifySubscribersAboutMapCreatedCommandValidationTest extends ValidationTestCase
{
    public function testCommandValidationFailedWithIncorrectMapId(): void
    {
        $invalidCommand = new NotifySubscribersAboutMapCreatedCommand(0);

        $this->getValidator()->validate($invalidCommand);

        $this->assertFieldInvalid('mapId', 'Точка на карте не найдена.');
    }

    public function testCommandValidationPassedWithCorrectMapId(): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadMaps::class,
        ])->getReferenceRepository();

        $correctMapId = $referenceRepository->getReference(LoadMaps::getRandReferenceName())->getId();

        $validCommand = new NotifySubscribersAboutMapCreatedCommand($correctMapId);

        $this->getValidator()->validate($validCommand);

        $this->assertEmpty($this->getValidator()->getLastErrors());
    }
}
