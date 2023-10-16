<?php

namespace Tests\Functional\Domain\EventSubscriber;

use App\Domain\Company\Command\RewriteCompanyAuthorNameCommand;
use App\Domain\Company\Entity\Company;
use App\Domain\Company\Event\CompanyUpdatedEvent;
use App\Domain\Company\Repository\CompanyRepository;
use App\Domain\EventSubscriber\CompanyEventsSubscriber;
use League\Tactician\CommandBus;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Tests\DataFixtures\ORM\Company\Company\LoadCompanyWithOwner;
use Tests\Functional\TestCase;
use Tests\Unit\Mock\CommandBusMock;

class CompanyEventsSubscriberTest extends TestCase
{
    private CommandBus $commandBusMock;
    private EventDispatcherInterface $eventDispatcher;
    private Company $company;
    private CompanyRepository $companyRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->commandBusMock = new CommandBusMock();
        $this->eventDispatcher = $this->getContainer()->get('event_dispatcher');
        $this->eventDispatcher->addSubscriber(new CompanyEventsSubscriber($this->commandBusMock));

        $referenceRepository = $this->loadFixtures([
            LoadCompanyWithOwner::class,
        ])->getReferenceRepository();

        $this->company = $referenceRepository->getReference(LoadCompanyWithOwner::REFERENCE_NAME);
        $this->companyRepository = $this->getContainer()->get(CompanyRepository::class);
    }

    public function testSyncCompanyNameWithCompanyAuthorNameAfterUpdateCompany(): void
    {
        $this->eventDispatcher->dispatch(new CompanyUpdatedEvent($this->company));

        $this->assertTrue($this->commandBusMock->isHandled(RewriteCompanyAuthorNameCommand::class));
    }
}
