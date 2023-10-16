<?php

namespace Tests\Functional\Domain\User\Command\Notification;

use App\Domain\User\Command\Notification\NotifySubscribersAboutTidingsCreatedCommand;
use Tests\DataFixtures\ORM\Record\Tidings\LoadNumberedTidings;
use Tests\Functional\ValidationTestCase;

/**
 * @group notification
 */
class NotifySubscribersAboutTidingsCreatedCommandValidationTest extends ValidationTestCase
{
    public function testCommandValidationFailedWithIncorrectTidingsId(): void
    {
        $invalidCommand = new NotifySubscribersAboutTidingsCreatedCommand(0);

        $this->getValidator()->validate($invalidCommand);

        $this->assertFieldInvalid('tidingsId', 'Весть с водоема не найдена.');
    }

    public function testCommandValidationPassedWithCorrectTidingsId(): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadNumberedTidings::class,
        ])->getReferenceRepository();

        $correctTidingsId = $referenceRepository->getReference(LoadNumberedTidings::getRandReferenceName())->getId();

        $validCommand = new NotifySubscribersAboutTidingsCreatedCommand($correctTidingsId);

        $this->getValidator()->validate($validCommand);

        $this->assertEmpty($this->getValidator()->getLastErrors());
    }
}
