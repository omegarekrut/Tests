<?php

namespace Tests\Functional\Domain\EventSubscriber;

use App\Domain\Company\Command\UpdateCompanyLatestRecordCreatedTimeCommand;
use App\Domain\EventSubscriber\RecordEventsSubscriber;
use App\Domain\Record\Common\Command\SemanticLink\DetachRecordSemanticLinksCommand;
use App\Domain\Record\Common\Command\Viewing\UpdateViewCountCommand;
use App\Domain\Record\Common\Entity\Record;
use App\Domain\Record\Common\Event\RecordAuthorChangedEvent;
use App\Domain\Record\Common\Event\RecordDeletedEvent;
use App\Domain\Record\Common\Event\RecordHideEvent;
use App\Domain\Record\Common\Event\RecordRestoreEvent;
use App\Domain\Record\Common\Event\RecordViewedEvent;
use App\Domain\Record\CompanyArticle\Entity\CompanyArticle;
use App\Domain\User\Command\Rating\RecalculateUserActivityRatingCommand;
use App\Domain\User\Entity\User;
use Doctrine\Common\DataFixtures\ReferenceRepository;
use League\Tactician\CommandBus;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Tests\DataFixtures\ORM\Record\CompanyArticle\LoadCompanyArticleForSemanticLinks;
use Tests\DataFixtures\ORM\Record\LoadArticles;
use Tests\DataFixtures\ORM\User\LoadUserWithoutRecords;
use Tests\Functional\TestCase;
use Tests\Unit\Mock\CommandBusMock;

/**
 * @group record-events
 */
class RecordEventSubscriberTest extends TestCase
{
    private CommandBus $commandBusMock;
    private EventDispatcherInterface $eventDispatcher;
    private ReferenceRepository $referenceRepository;
    private Record $article;

    protected function setUp(): void
    {
        parent::setUp();

        $container = $this->getContainer();

        $this->commandBusMock = new CommandBusMock();
        $this->eventDispatcher = $container->get('event_dispatcher');
        $this->eventDispatcher->addSubscriber(new RecordEventsSubscriber($this->commandBusMock));

        $this->referenceRepository = $this->loadFixtures([
            LoadArticles::class,
            LoadUserWithoutRecords::class,
        ])->getReferenceRepository();

        $this->article = $this->referenceRepository->getReference(LoadArticles::getRandReferenceName());
    }

    protected function tearDown(): void
    {
        unset(
            $this->commandBusMock,
            $this->eventDispatcher,
            $this->referenceRepository,
            $this->article
        );

        parent::tearDown();
    }

    public function testIncrementViewCount(): void
    {
        $this->eventDispatcher->dispatch(new RecordViewedEvent($this->article));

        $this->assertTrue($this->commandBusMock->isHandled(UpdateViewCountCommand::class));
    }

    public function testCallRecalculateRatingActivityForAuthorWhenRecordHiding(): void
    {
        $this->eventDispatcher->dispatch(new RecordHideEvent($this->article));

        $this->assertTrue($this->commandBusMock->isHandled(RecalculateUserActivityRatingCommand::class));
    }

    public function testDetachRecordSemanticLinksCommandIsHandledWhenHiding(): void
    {
        $this->eventDispatcher->dispatch(new RecordHideEvent($this->article));

        $this->assertTrue($this->commandBusMock->isHandled(DetachRecordSemanticLinksCommand::class));
    }

    public function testCallRecalculateRatingActivityForAuthorWhenRecordRestoring(): void
    {
        $this->eventDispatcher->dispatch(new RecordRestoreEvent($this->article));

        $this->assertTrue($this->commandBusMock->isHandled(RecalculateUserActivityRatingCommand::class));
    }

    public function testRatingChangeAfterAuthorChange(): void
    {
        $oldAuthor = $this->article->getAuthor();
        assert($oldAuthor instanceof User);
        $newAuthor = $this->referenceRepository->getReference(LoadUserWithoutRecords::REFERENCE_NAME);
        assert($newAuthor instanceof User);

        $this->eventDispatcher->dispatch(new RecordAuthorChangedEvent($this->article, $oldAuthor, $newAuthor));

        /** @var RecalculateUserActivityRatingCommand[] $recalculateUserActivityRatingCommands */
        $recalculateUserActivityRatingCommands = $this->commandBusMock->getAllHandledCommandsOfClass(RecalculateUserActivityRatingCommand::class);

        $this->assertCount(2, $recalculateUserActivityRatingCommands);

        $handledRecalculateRatingUserIds = array_map(
            fn (RecalculateUserActivityRatingCommand $command): int => $command->userId,
            $recalculateUserActivityRatingCommands
        );

        $this->assertContains($oldAuthor->getId(), $handledRecalculateRatingUserIds);
        $this->assertContains($newAuthor->getId(), $handledRecalculateRatingUserIds);
    }

    public function testCallRecalculateRatingActivityAfterChangeAuthor(): void
    {
        $oldAuthor = $this->article->getAuthor();
        assert($oldAuthor instanceof User);
        $newAuthor = $this->referenceRepository->getReference(LoadUserWithoutRecords::REFERENCE_NAME);
        assert($newAuthor instanceof User);

        $this->eventDispatcher->dispatch(new RecordAuthorChangedEvent($this->article, $oldAuthor, $newAuthor));

        /** @var RecalculateUserActivityRatingCommand[] $recalculateUserActivityRatingCommands */
        $recalculateUserActivityRatingCommands = $this->commandBusMock->getAllHandledCommandsOfClass(RecalculateUserActivityRatingCommand::class);

        $this->assertCount(2, $recalculateUserActivityRatingCommands);

        $handledRecalculateRatingUserIds = array_map(
            fn (RecalculateUserActivityRatingCommand $command): int => $command->userId,
            $recalculateUserActivityRatingCommands
        );

        $this->assertContains($oldAuthor->getId(), $handledRecalculateRatingUserIds);
        $this->assertContains($newAuthor->getId(), $handledRecalculateRatingUserIds);
    }

    public function testCallUpdateCompanyLastRecordCreatedAtOnRecordHide(): void
    {
        $article = $this->loadFixture(LoadCompanyArticleForSemanticLinks::class, CompanyArticle::class);
        $event = new RecordHideEvent($article);

        $this->eventDispatcher->dispatch($event);

        $this->assertTrue($this->commandBusMock->isHandled(UpdateCompanyLatestRecordCreatedTimeCommand::class));
    }

    public function testCallUpdateCompanyLastRecordCreatedAtOnRecordDelete(): void
    {
        $article = $this->loadFixture(LoadCompanyArticleForSemanticLinks::class, CompanyArticle::class);
        $event = new RecordDeletedEvent($article);

        $this->eventDispatcher->dispatch($event);

        $this->assertTrue($this->commandBusMock->isHandled(UpdateCompanyLatestRecordCreatedTimeCommand::class));
    }

    public function testCallUpdateCompanyLastRecordCreatedAtOnRecordRestore(): void
    {
        $article = $this->loadFixture(LoadCompanyArticleForSemanticLinks::class, CompanyArticle::class);
        $event = new RecordRestoreEvent($article);

        $this->eventDispatcher->dispatch($event);

        $this->assertTrue($this->commandBusMock->isHandled(UpdateCompanyLatestRecordCreatedTimeCommand::class));
    }
}
