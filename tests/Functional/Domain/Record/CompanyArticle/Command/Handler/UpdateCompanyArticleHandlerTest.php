<?php

namespace Tests\Functional\Domain\Record\CompanyArticle\Command\Handler;

use App\Domain\Record\CompanyArticle\Command\UpdateCompanyArticleCommand;
use App\Domain\Record\CompanyArticle\Entity\CompanyArticle;
use App\Domain\Record\CompanyArticle\Event\CompanyArticleUpdatedEvent;
use App\Domain\Record\CompanyArticle\Repository\CompanyArticleRepository;
use App\Util\ImageStorage\Collection\ImageCollection;
use League\Tactician\CommandBus;
use Tests\DataFixtures\ORM\Record\CompanyArticle\LoadCompanyArticleWherePublishedLater;
use Tests\Functional\TestCase;
use Tests\DataFixtures\ORM\Record\CompanyArticle\LoadAquaMotorcycleShopsCompanyArticle;

/**
 * @group company
 */
class UpdateCompanyArticleHandlerTest extends TestCase
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
        $companyArticle = $this->loadCompanyArticle();
        $command = $this->createUpdateCompanyArticleCommand($companyArticle);

        $this->commandBus->handle($command);
        $this->getArticleFromRepository($command);

        $this->assertEquals($command->title, $companyArticle->getTitle());
        $this->assertEquals($command->text, $companyArticle->getText());
        $this->assertEquals($command->images, $companyArticle->getImages());
        $this->assertEquals($command->youtubeVideoUrls, $companyArticle->getVideoUrls()->toArray());
        $this->assertEquals($command->publishAt, $companyArticle->getPublishAt());
    }

    public function testHandleDMustCallUpdateEvent(): void
    {
        $companyArticle = $this->loadCompanyArticle();
        $command = $this->createUpdateCompanyArticleCommand($companyArticle);

        $this->commandBus->handle($command);

        $this->assertContains(CompanyArticleUpdatedEvent::class, $this->getDispatchedEvents());
    }

    public function testHandleDelayedCompanyArticleMustNotCallUpdateEvent(): void
    {
        $companyArticle = $this->loadDelayedCompanyArticle();
        $command = $this->createUpdateCompanyArticleCommand($companyArticle);

        $this->commandBus->handle($command);

        $this->assertNotContains(CompanyArticleUpdatedEvent::class, $this->getDispatchedEvents());
    }

    private function createUpdateCompanyArticleCommand(CompanyArticle $companyArticle): UpdateCompanyArticleCommand
    {
        $command = new UpdateCompanyArticleCommand($companyArticle);
        $command->title = 'New company article title';
        $command->text = 'New company article text';
        $command->images = new ImageCollection([]);
        $command->youtubeVideoUrls = [];

        return $command;
    }

    private function getArticleFromRepository(UpdateCompanyArticleCommand $command): CompanyArticle
    {
        $companyArticleRepository = $this->getContainer()->get(CompanyArticleRepository::class);
        assert($companyArticleRepository instanceof CompanyArticleRepository);

        return $companyArticleRepository->findByTitle($command->title);
    }

    private function loadCompanyArticle(): CompanyArticle
    {
        return $this->loadFixture(LoadAquaMotorcycleShopsCompanyArticle::class, CompanyArticle::class);
    }

    private function loadDelayedCompanyArticle(): CompanyArticle
    {
        return $this->loadFixture(LoadCompanyArticleWherePublishedLater::class, CompanyArticle::class);
    }

    /**
     * @return string[]
     */
    private function getDispatchedEvents(): array
    {
        return array_map(static fn ($listener) => $listener['event'], $this->getEventDispatcher()->getCalledListeners());
    }
}
