<?php

namespace Tests\Functional\Domain\Record\CompanyArticle\Command\Handler;

use App\Domain\Company\Entity\Company;
use App\Domain\Record\CompanyArticle\Command\CreateCompanyArticleCommand;
use App\Domain\Record\CompanyArticle\Entity\CompanyArticle;
use App\Domain\Record\CompanyArticle\Event\CompanyArticlePublishedEvent;
use App\Domain\Record\CompanyArticle\Repository\CompanyArticleRepository;
use App\Module\Author\AuthorInterface;
use App\Util\ImageStorage\Collection\ImageCollection;
use Carbon\Carbon;
use League\Tactician\CommandBus;
use Tests\DataFixtures\ORM\Company\Company\LoadCompanyWithOwner;
use Tests\DataFixtures\ORM\Company\CompanyWithSubscription\LoadCompanyWithActiveSubscriptionWithUser;
use Tests\Functional\TestCase;

/**
 * @group company
 */
class CreateCompanyArticleHandlerTest extends TestCase
{
    private CommandBus $commandBus;

    public function setUp(): void
    {
        parent::setUp();

        $this->clearDatabase();

        $this->commandBus = $this->getCommandBus();
    }

    public function testHandle(): void
    {
        $company = $this->loadCompanyWithOwner();
        $command = $this->createCreateCompanyArticleCommand($company, $company->getOwner());

        $this->commandBus->handle($command);
        $companyArticle = $this->getArticleFromRepository($command);

        $this->assertEquals($command->title, $companyArticle->getTitle());
        $this->assertEquals($command->text, $companyArticle->getText());
        $this->assertEquals($command->images, $companyArticle->getImages());
        $this->assertEquals($command->youtubeVideoUrls, $companyArticle->getVideoUrls()->toArray());
        $this->assertEquals($command->publishAt, $companyArticle->getPublishAt());
    }

    public function testHandleMustCallPublishEvent(): void
    {
        $company = $this->loadCompanyWithOwner();
        $command = $this->createCreateCompanyArticleCommand($company, $company->getOwner());

        $this->commandBus->handle($command);

        $this->assertContains(CompanyArticlePublishedEvent::class, $this->getDispatchedEvents());
    }

    public function testHandleDelayedCompanyArticleMustNotCallPublishEvent(): void
    {
        $company = $this->loadCompanyWithOwner();
        $command = $this->createDelayedCompanyArticleCommand($company, $company->getOwner());

        $this->commandBus->handle($command);

        $this->assertNotContains(CompanyArticlePublishedEvent::class, $this->getDispatchedEvents());
    }

    public function testArticleOfCompanyWithoutSubscriptionMustNotMarkedAsInteresting(): void
    {
        $company = $this->loadCompanyWithOwner();
        $command = $this->createCreateCompanyArticleCommand($company, $company->getOwner());

        $this->commandBus->handle($command);
        $companyArticle = $this->getArticleFromRepository($command);

        $this->assertFalse($companyArticle->isShowInInteresting());
    }

    public function testArticleOfCompanyWithSubscriptionMustMarkedAsInteresting(): void
    {
        $company = $this->loadCompanyWithSubscription();
        $command = $this->createCreateCompanyArticleCommand($company, $company->getOwner());

        $this->commandBus->handle($command);
        $companyArticle = $this->getArticleFromRepository($command);

        $this->assertTrue($companyArticle->isShowInInteresting());
    }

    private function createCreateCompanyArticleCommand(Company $company, AuthorInterface $author): CreateCompanyArticleCommand
    {
        $title = 'Company article title';

        $command = new CreateCompanyArticleCommand();
        $command->author = $author;
        $command->company = $company;
        $command->title = $title;
        $command->text = 'Company article text';
        $command->images = new ImageCollection([]);
        $command->youtubeVideoUrls = [];

        return $command;
    }

    private function getArticleFromRepository(CreateCompanyArticleCommand $command): CompanyArticle
    {
        $companyArticleRepository = $this->getContainer()->get(CompanyArticleRepository::class);
        assert($companyArticleRepository instanceof CompanyArticleRepository);

        return $companyArticleRepository->findByTitle($command->title);
    }

    private function createDelayedCompanyArticleCommand(Company $company, AuthorInterface $author): CreateCompanyArticleCommand
    {
        $command = $this->createCreateCompanyArticleCommand($company, $author);
        $command->publishAt = Carbon::now()->addWeek();

        return $command;
    }

    private function loadCompanyWithOwner(): Company
    {
        return $this->loadFixture(LoadCompanyWithOwner::class, Company::class);
    }

    private function loadCompanyWithSubscription(): Company
    {
        return $this->loadFixture(LoadCompanyWithActiveSubscriptionWithUser::class, Company::class);
    }

    /**
     * @return string[]
     */
    private function getDispatchedEvents(): array
    {
        return array_map(static fn ($listener) => $listener['event'], $this->getEventDispatcher()->getCalledListeners());
    }
}
