<?php

namespace Tests\Functional\Domain\EventSubscriber;

use App\Domain\Company\Command\UpdateRegionOfCompanyViaLocationCommand;
use App\Domain\Company\Event\LocationUpdatedEvent;
use App\Domain\EventSubscriber\LocationEventSubscriber;
use League\Tactician\CommandBus;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Tests\DataFixtures\ORM\Company\Company\LoadCompanyWithFixedCoordinates;
use Tests\Functional\TestCase;
use Tests\Unit\Mock\CommandBusMock;

class LocationEventSubscriberTest extends TestCase
{
    private CommandBus $commandBusMock;
    private EventDispatcherInterface $eventDispatcher;

    protected function setUp(): void
    {
        parent::setUp();

        $this->commandBusMock = new CommandBusMock();
        $this->eventDispatcher = $this->getContainer()->get('event_dispatcher');
        $this->eventDispatcher->addSubscriber(new LocationEventSubscriber($this->commandBusMock));
    }

    protected function tearDown(): void
    {
        unset(
            $this->commandBusMock,
            $this->eventDispatcher,
        );

        parent::tearDown();
    }

    public function testUpdateRegionWithValidCompanyLocation(): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadCompanyWithFixedCoordinates::class,
        ])->getReferenceRepository();

        $companyWithLocation = $referenceRepository->getReference(LoadCompanyWithFixedCoordinates::REFERENCE_NAME);

        $this->eventDispatcher->dispatch(new LocationUpdatedEvent($companyWithLocation));

        $this->assertTrue($this->commandBusMock->isHandled(UpdateRegionOfCompanyViaLocationCommand::class));
    }
}
