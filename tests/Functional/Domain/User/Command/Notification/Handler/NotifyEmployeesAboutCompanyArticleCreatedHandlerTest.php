<?php

namespace Tests\Functional\Domain\User\Command\Notification\Handler;

use App\Domain\Record\CompanyArticle\Entity\CompanyArticle;
use App\Domain\User\Collection\NotificationCollection;
use App\Domain\User\Command\Notification\NotifyEmployeesAboutCompanyArticleCreatedCommand;
use App\Domain\User\Command\Notification\NotifySubscribersAboutCompanyArticleCreatedCommand;
use App\Domain\User\Entity\Notification\CompanyArticleCreatedNotification;
use App\Domain\User\Entity\Notification\Notification;
use App\Domain\User\Entity\User;
use Tests\DataFixtures\ORM\Company\Company\LoadAquaMotorcycleShopsCompany;
use Tests\DataFixtures\ORM\Company\Company\LoadManySimpleOwnedCompanies;
use Tests\DataFixtures\ORM\Record\CompanyArticle\LoadAquaMotorcycleShopsCompanyArticle;
use Tests\DataFixtures\ORM\Record\CompanyArticle\LoadCompanyArticleWherePublishedLater;
use Tests\DataFixtures\ORM\User\LoadUserWithAvatar;
use Tests\Functional\TestCase;

/**
 * @group notification
 */
class NotifyEmployeesAboutCompanyArticleCreatedHandlerTest extends TestCase
{
    private CompanyArticle $companyArticle;
    private CompanyArticle $companyArticleWherePublishedLater;
    private User $companyEmployee;

    protected function setUp(): void
    {
        parent::setUp();

        $referenceRepository = $this->loadFixtures([
            LoadAquaMotorcycleShopsCompanyArticle::class,
            LoadCompanyArticleWherePublishedLater::class,
            LoadAquaMotorcycleShopsCompany::class,
            LoadManySimpleOwnedCompanies::class,
            LoadUserWithAvatar::class,
        ])->getReferenceRepository();

        $this->companyArticle = $referenceRepository->getReference(LoadAquaMotorcycleShopsCompanyArticle::REFERENCE_NAME);
        $this->companyArticleWherePublishedLater = $referenceRepository->getReference(LoadCompanyArticleWherePublishedLater::REFERENCE_NAME);
        $this->companyEmployee = $referenceRepository->getReference(LoadUserWithAvatar::REFERENCE_NAME);
        $aquaMotorcycleShopsCompany = $referenceRepository->getReference(LoadAquaMotorcycleShopsCompany::REFERENCE_NAME);
        $manySimpleOwnedCompanies = $referenceRepository->getReference(LoadManySimpleOwnedCompanies::COMPANY_PREFIX_REFERENCE.'-1');

        $aquaMotorcycleShopsCompany->addEmployee($this->companyEmployee);
        $manySimpleOwnedCompanies->addEmployee($this->companyEmployee);
    }

    public function testEmployeeMustReceiveNotificationAfterHandle(): void
    {
        $command = new NotifyEmployeesAboutCompanyArticleCreatedCommand($this->companyArticle->getId());

        $this->getCommandBus()->handle($command);

        /** @var Notification|null $actualNotification */
        $actualNotification = $this->companyEmployee->getUnreadNotifications()->first();

        $this->assertInstanceOf(CompanyArticleCreatedNotification::class, $actualNotification);
        assert($actualNotification instanceof CompanyArticleCreatedNotification);

        $this->assertTrue($this->companyArticle === $actualNotification->getCompanyArticle());
    }

    public function testEmployeeShouldNotReceiveNotificationAfterHandleIfCompanyArticlePublishedLater(): void
    {
        $command = new NotifySubscribersAboutCompanyArticleCreatedCommand($this->companyArticleWherePublishedLater->getId());

        $this->getCommandBus()->handle($command);

        $actualNotifications = $this->companyEmployee->getUnreadNotifications();
        assert($actualNotifications instanceof NotificationCollection);

        $this->assertCount(0, $actualNotifications);
    }
}
