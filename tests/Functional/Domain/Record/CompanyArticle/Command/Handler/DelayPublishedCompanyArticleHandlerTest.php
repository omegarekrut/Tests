<?php

namespace Tests\Functional\Domain\Record\CompanyArticle\Command\Handler;

use App\Domain\Record\CompanyArticle\Command\DelayPublishedCompanyArticleCommand;
use App\Domain\Record\CompanyArticle\Entity\CompanyArticle;
use App\Domain\Record\CompanyArticle\Event\CompanyArticlePublishedEvent;
use Carbon\Carbon;
use League\Tactician\CommandBus;
use Tests\DataFixtures\ORM\Record\CompanyArticle\LoadCompanyArticleWherePublishedLater;
use Tests\Functional\TestCase;

/**
 * @group company
 */
class DelayPublishedCompanyArticleHandlerTest extends TestCase
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
        $companyArticle = $this->loadFixture(LoadCompanyArticleWherePublishedLater::class, CompanyArticle::class);

        $command = $this->createDelayPublishedCompanyArticleCommand($companyArticle);
        $command->publishAt = $companyArticle->getPublishAt();

        $this->commandBus->handle($command);

        $this->assertContains(CompanyArticlePublishedEvent::class, $this->getDispatchedEvents());
    }

    public function testHandleWithChangedPublishAtMustNotCallPublishEvent(): void
    {
        $companyArticle = $this->loadFixture(LoadCompanyArticleWherePublishedLater::class, CompanyArticle::class);

        $command = $this->createDelayPublishedCompanyArticleCommand($companyArticle);
        $command->publishAt = Carbon::create($companyArticle->getPublishAt())->addSecond();

        $this->commandBus->handle($command);

        $this->assertNotContains(CompanyArticlePublishedEvent::class, $this->getDispatchedEvents());
    }

    private function createDelayPublishedCompanyArticleCommand(CompanyArticle $companyArticle): DelayPublishedCompanyArticleCommand
    {
        $command = new DelayPublishedCompanyArticleCommand();
        $command->companyArticleId = $companyArticle->getId();

        return $command;
    }

    /**
     * @return string[]
     */
    private function getDispatchedEvents(): array
    {
        return array_map(static fn ($listener) => $listener['event'], $this->getEventDispatcher()->getCalledListeners());
    }
}
