<?php

namespace Tests\Functional\Domain\User\Command\Notification;

use App\Domain\User\Command\Notification\NotifySubscribersAboutArticleCreatedCommand;
use Tests\DataFixtures\ORM\Record\LoadArticles;
use Tests\Functional\ValidationTestCase;

/**
 * @group notification
 */
class NotifySubscribersAboutArticleCreatedCommandValidationTest extends ValidationTestCase
{
    public function testCommandValidationFailedWithIncorrectArticleId(): void
    {
        $invalidCommand = new NotifySubscribersAboutArticleCreatedCommand(0);

        $this->getValidator()->validate($invalidCommand);

        $this->assertFieldInvalid('articleId', 'Запись не найдена.');
    }

    public function testCommandValidationPassedWithCorrectArticleId(): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadArticles::class,
        ])->getReferenceRepository();

        $correctArticleId = $referenceRepository->getReference(LoadArticles::getRandReferenceName())->getId();

        $validCommand = new NotifySubscribersAboutArticleCreatedCommand($correctArticleId);

        $this->getValidator()->validate($validCommand);

        $this->assertEmpty($this->getValidator()->getLastErrors());
    }
}
