<?php

namespace Tests\Functional\Domain\User\Command\Notification;

use App\Domain\User\Command\Notification\NotifyEmployeesAboutCompanyArticleCreatedCommand;
use Tests\DataFixtures\ORM\Record\CompanyArticle\LoadAquaMotorcycleShopsCompanyArticle;
use Tests\Functional\ValidationTestCase;

/**
 * @group notification
 */
class NotifyEmployeesAboutArticleCreatedCommandValidationTest extends ValidationTestCase
{
    public function testCommandValidationFailedWithIncorrectArticleId(): void
    {
        $invalidCommand = new NotifyEmployeesAboutCompanyArticleCreatedCommand(0);

        $this->getValidator()->validate($invalidCommand);

        $this->assertFieldInvalid('companyArticleId', 'Статья не найдена.');
    }

    public function testCommandValidationPassedWithCorrectArticleId(): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadAquaMotorcycleShopsCompanyArticle::class,
        ])->getReferenceRepository();

        $correctCompanyArticleId = $referenceRepository->getReference(LoadAquaMotorcycleShopsCompanyArticle::REFERENCE_NAME)->getId();

        $validCommand = new NotifyEmployeesAboutCompanyArticleCreatedCommand($correctCompanyArticleId);

        $this->getValidator()->validate($validCommand);

        $this->assertEmpty($this->getValidator()->getLastErrors());
    }
}
