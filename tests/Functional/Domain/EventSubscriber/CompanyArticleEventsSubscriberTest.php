<?php

namespace Tests\Functional\Domain\EventSubscriber;

use App\Domain\Company\Command\UpdateCompanyLatestRecordCreatedTimeCommand;
use App\Domain\Company\Entity\Company;
use App\Domain\EventSubscriber\CompanyArticleEventsSubscriber;
use App\Domain\Hashtag\Command\UpdateHashtagsByRecordTextCommand;
use App\Domain\Record\CompanyArticle\Command\CreateCompanyArticleCommand;
use App\Domain\Record\CompanyArticle\Command\SemanticLink\SyncCompanyArticleSemanticLinksWithTextCommand;
use App\Domain\Record\CompanyArticle\Command\SocialNetwork\PublishCompanyArticleInSocialNetworkCommand;
use App\Domain\Record\CompanyArticle\Entity\CompanyArticle;
use App\Domain\Record\CompanyArticle\Event\CompanyArticlePublishedEvent;
use App\Domain\Record\CompanyArticle\Event\CompanyArticleUpdatedEvent;
use App\Domain\Record\CompanyArticle\Repository\CompanyArticleRepository;
use App\Domain\User\Command\Notification\NotifyEmployeesAboutCompanyArticleCreatedCommand;
use App\Domain\User\Command\Notification\NotifySubscribersAboutCompanyArticleCreatedCommand;
use App\Domain\User\Command\Notification\NotifyUsersAboutCompanyCreatedCommand;
use App\Util\ImageStorage\Collection\ImageCollection;
use League\Tactician\CommandBus;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Tests\DataFixtures\ORM\Company\Company\LoadAquaMotorcycleShopsCompany;
use Tests\DataFixtures\ORM\Company\CompanyWithSubscription\LoadCompanyWithActiveSubscriptionWithUser;
use Tests\DataFixtures\ORM\Record\CompanyArticle\LoadCompanyArticleForSemanticLinks;
use Tests\DataFixtures\ORM\Record\CompanyArticle\LoadCompanyArticleWithAuthor;
use Tests\Functional\TestCase;
use Tests\Unit\Mock\CommandBusMock;

/**
 * @group company-article-events
 */
class CompanyArticleEventsSubscriberTest extends TestCase
{
    private CommandBus $commandBusMock;
    private EventDispatcherInterface $eventDispatcher;

    protected function setUp(): void
    {
        parent::setUp();

        $this->clearDatabase();

        $this->commandBusMock = new CommandBusMock();

        $this->eventDispatcher = $this->getEventDispatcher();
        $this->eventDispatcher->addSubscriber(new CompanyArticleEventsSubscriber($this->commandBusMock));

        $companyArticleRepository = $this->getContainer()->get(CompanyArticleRepository::class);
        assert($companyArticleRepository instanceof CompanyArticleRepository);

        $this->companyArticleRepository = $companyArticleRepository;
    }

    public function testNotificationMustBeSentAfterFirstArticleOfCompanyPublished(): void
    {
        $firstArticleOfCompany = $this->loadFirstArticleOfCompany();

        $this->eventDispatcher->dispatch(new CompanyArticlePublishedEvent($firstArticleOfCompany));

        $this->assertTrue($this->commandBusMock->isHandled(NotifyUsersAboutCompanyCreatedCommand::class));
    }

    public function testNotificationMustNotBeSentAfterNotFirstArticleOfCompanyPublished(): void
    {
        $notFirstArticleOfCompany = $this->loadNotFirstArticleOfCompany();

        $this->eventDispatcher->dispatch(new CompanyArticlePublishedEvent($notFirstArticleOfCompany));

        $this->assertFalse($this->commandBusMock->isHandled(NotifyUsersAboutCompanyCreatedCommand::class));
    }

    public function testNotificationForSubscribersAndEmployeesMustBeSentAfterArticleOfCompanyPublished(): void
    {
        $companyArticle = $this->loadCompanyArticle();

        $this->eventDispatcher->dispatch(new CompanyArticlePublishedEvent($companyArticle));

        $this->assertTrue($this->commandBusMock->isHandled(NotifySubscribersAboutCompanyArticleCreatedCommand::class));
        $this->assertTrue($this->commandBusMock->isHandled(NotifyEmployeesAboutCompanyArticleCreatedCommand::class));
    }

    public function testUpdateHashtagsAndSyncCompanyArticleSemanticLinksAfterPublishArticleOfCompany(): void
    {
        $companyArticle = $this->loadCompanyArticle();

        $this->eventDispatcher->dispatch(new CompanyArticlePublishedEvent($companyArticle));

        $this->assertTrue($this->commandBusMock->isHandled(UpdateHashtagsByRecordTextCommand::class));
        $this->assertTrue($this->commandBusMock->isHandled(SyncCompanyArticleSemanticLinksWithTextCommand::class));
    }

    public function testUpdateHashtagsAndSyncCompanyArticleSemanticLinksAfterUpdateArticleOfCompany(): void
    {
        $companyArticle = $this->loadCompanyArticle();

        $this->eventDispatcher->dispatch(new CompanyArticleUpdatedEvent($companyArticle));

        $this->assertTrue($this->commandBusMock->isHandled(UpdateHashtagsByRecordTextCommand::class));
        $this->assertTrue($this->commandBusMock->isHandled(SyncCompanyArticleSemanticLinksWithTextCommand::class));
    }

    public function testPublishInSocialNetworkCompanyArticleOfCompanyWithSubscription(): void
    {
        $companyWithSubscription = $this->loadCompanyWithSubscription();
        $companyArticle = $this->createArticleForCompany($companyWithSubscription);

        $this->eventDispatcher->dispatch(new CompanyArticlePublishedEvent($companyArticle));

        $this->assertTrue($this->commandBusMock->isHandled(PublishCompanyArticleInSocialNetworkCommand::class));
    }

    private function createArticleForCompany(Company $company): CompanyArticle
    {
        $createCompanyArticleCommand = new CreateCompanyArticleCommand();
        $createCompanyArticleCommand->author = $company->getOwner();
        $createCompanyArticleCommand->company = $company;
        $createCompanyArticleCommand->title = $this->getFaker()->realText(20);
        $createCompanyArticleCommand->text = $this->getFaker()->realText(200);
        $createCompanyArticleCommand->images = new ImageCollection([]);
        $createCompanyArticleCommand->youtubeVideoUrls = [];

        $this->getCommandBus()->handle($createCompanyArticleCommand);

        $companyArticle = $this->companyArticleRepository->findByTitle($createCompanyArticleCommand->title);
        assert($companyArticle instanceof CompanyArticle);

        return $companyArticle;
    }

    private function loadCompanyWithSubscription(): Company
    {
        $referenceRepository = $this->loadFixtures([LoadCompanyWithActiveSubscriptionWithUser::class])->getReferenceRepository();
        $companyWithSubscription = $referenceRepository->getReference(LoadCompanyWithActiveSubscriptionWithUser::getReferenceName());
        assert($companyWithSubscription instanceof Company);

        return $companyWithSubscription;
    }

    private function loadFirstArticleOfCompany(): CompanyArticle
    {
        $referenceRepository = $this->loadFixtures([LoadAquaMotorcycleShopsCompany::class, LoadCompanyArticleForSemanticLinks::class])->getReferenceRepository();
        $firstArticle = $referenceRepository->getReference(LoadCompanyArticleForSemanticLinks::getReferenceName());
        assert($firstArticle instanceof CompanyArticle);

        return $firstArticle;
    }

    private function loadNotFirstArticleOfCompany(): CompanyArticle
    {
        $referenceRepository = $this->loadFixtures([
            LoadAquaMotorcycleShopsCompany::class,
            LoadCompanyArticleForSemanticLinks::class,
            LoadCompanyArticleWithAuthor::class,
        ])->getReferenceRepository();

        $secondCompanyArticle = $referenceRepository->getReference(LoadCompanyArticleWithAuthor::getReferenceName());
        assert($secondCompanyArticle instanceof CompanyArticle);

        return $secondCompanyArticle;
    }

    private function loadCompanyArticle(): CompanyArticle
    {
        return $this->loadFixture(LoadCompanyArticleForSemanticLinks::class, CompanyArticle::class);
    }

    public function testUpdatesCompanyLastRecordCreatedAtOnArticlePublished()
    {
        $companyArticle = $this->loadCompanyArticle();

        $this->eventDispatcher->dispatch(new CompanyArticlePublishedEvent($companyArticle));

        $this->assertTrue($this->commandBusMock->isHandled(UpdateCompanyLatestRecordCreatedTimeCommand::class));
    }

    public function testUpdatesCompanyLastRecordCreatedAtOnArticleUpdated()
    {
        $companyArticle = $this->loadCompanyArticle();

        $this->eventDispatcher->dispatch(new CompanyArticleUpdatedEvent($companyArticle));

        $this->assertTrue($this->commandBusMock->isHandled(UpdateCompanyLatestRecordCreatedTimeCommand::class));
    }
}
