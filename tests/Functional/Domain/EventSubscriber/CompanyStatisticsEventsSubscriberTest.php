<?php

namespace Tests\Functional\Domain\EventSubscriber;

use App\Domain\Company\Command\Statistics\IncreaseCountInStatisticsCompanyByTypeCommand;
use App\Domain\Company\Entity\Company;
use App\Domain\Company\Entity\Statistics\ValueObject\StatisticsType;
use App\Domain\Company\Event\IncrementCountEvent;
use App\Domain\EventSubscriber\CompanyStatisticsEventsSubscriber;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use League\Tactician\CommandBus;
use Tests\DataFixtures\ORM\Company\Company\LoadCompanyWithFixedCoordinates;
use Tests\Functional\TestCase;
use Tests\Unit\Mock\CommandBusMock;

class CompanyStatisticsEventsSubscriberTest extends TestCase
{
    private CommandBus $commandBus;
    private EventDispatcherInterface $eventDispatcher;

    protected function setUp(): void
    {
        parent::setUp();

        $this->commandBus = new CommandBusMock();
        $this->eventDispatcher = $this->getContainer()->get('event_dispatcher');
        $this->eventDispatcher->addSubscriber(new CompanyStatisticsEventsSubscriber($this->commandBus));
    }

    protected function tearDown(): void
    {
        unset(
            $this->commandBus,
            $this->eventDispatcher,
        );

        parent::tearDown();
    }

    /**
     * @dataProvider statisticsTypeDataProvider
     */
    public function testIncrementCompanyStatistics(string $statisticsType): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadCompanyWithFixedCoordinates::class,
        ])->getReferenceRepository();

        $company = $referenceRepository->getReference(LoadCompanyWithFixedCoordinates::REFERENCE_NAME);
        assert($company instanceof Company);

        $this->eventDispatcher->dispatch(new IncrementCountEvent($company, StatisticsType::from($statisticsType)));

        $this->assertTrue($this->commandBus->isHandled(IncreaseCountInStatisticsCompanyByTypeCommand::class));
    }

    /**
     * @return string[]
     */
    public function statisticsTypeDataProvider(): array
    {
        $data = [];

        foreach (StatisticsType::values() as $statisticsTypeKey => $nameOfStatisticsType) {
            $data[$statisticsTypeKey] = [(string) $nameOfStatisticsType];
        }

        return $data;
    }
}
