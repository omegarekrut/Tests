<?php

namespace Tests\Functional\Domain\Company\Command\Statistics\Handler;

use App\Domain\Company\Command\Statistics\IncreaseCountInStatisticsCompanyByTypeCommand;
use App\Domain\Company\Entity\Company;
use App\Domain\Company\Entity\Statistics\CompanyCardStatistics;
use App\Domain\Company\Entity\Statistics\TransferObject\AggregateKeyCompanyCardStatisticsByDateAndType;
use App\Domain\Company\Entity\Statistics\ValueObject\StatisticsType;
use App\Domain\Company\Repository\CompanyCardStatisticsRepository;
use Carbon\Carbon;
use DateTimeInterface;
use Tests\DataFixtures\ORM\Company\Company\LoadSetOfSimilarCompanies;
use Tests\DataFixtures\ORM\Company\Statistics\LoadCompanyCardStatistics;
use Tests\Functional\TestCase;

class IncreaseCountInStatisticsCompanyByTypeHandlerTest extends TestCase
{
    private CompanyCardStatisticsRepository $companyCardStatisticsRepository;
    private Company $company;
    private DateTimeInterface $trackingDate;

    protected function setUp(): void
    {
        parent::setUp();

        $referenceRepository = $this->loadFixtures([
            LoadSetOfSimilarCompanies::class,
            LoadCompanyCardStatistics::class,
        ])->getReferenceRepository();

        $companyCardStatisticsRepository = $this->getContainer()->get(CompanyCardStatisticsRepository::class);
        assert($companyCardStatisticsRepository instanceof CompanyCardStatisticsRepository);
        $this->companyCardStatisticsRepository = $companyCardStatisticsRepository;

        $company = $referenceRepository->getReference(LoadSetOfSimilarCompanies::ORIGINAL_COMPANY_REFERENCE);
        assert($company instanceof Company);
        $this->company = $company;

        $this->trackingDate = Carbon::today();
    }

    protected function tearDown(): void
    {
        unset(
            $this->companyCardStatisticsRepository,
            $this->company,
            $this->trackingDate
        );

        parent::tearDown();
    }

    /**
     * @dataProvider statisticsTypeDataProvider
     */
    public function testStatisticsShouldAddView(string $statisticsType): void
    {
        $command = new IncreaseCountInStatisticsCompanyByTypeCommand($this->company, $statisticsType);
        $companyCardStatistics = $this->getCompanyCardStatistics(
            $command->company,
            $this->trackingDate,
            StatisticsType::from($command->statisticsType)
        );
        $countOfCompanyCardStatistics = $companyCardStatistics->getCount();

        $this->getCommandBus()->handle($command);

        $expectedCompanyCardStatistics = $this->getCompanyCardStatistics(
            $command->company,
            $this->trackingDate,
            StatisticsType::from($command->statisticsType)
        );

        $this->assertEquals($countOfCompanyCardStatistics + 1, $expectedCompanyCardStatistics->getCount());
    }

    /**
     * @return string[]
     */
    public function statisticsTypeDataProvider(): array
    {
        $data = [];

        foreach (StatisticsType::toArray() as $statisticsTypeKey => $nameOfStatisticsType) {
            $data[$statisticsTypeKey] = [$nameOfStatisticsType];
        }

        return $data;
    }

    private function getCompanyCardStatistics(Company $company, DateTimeInterface $trackingDate, StatisticsType $statisticsType): ?CompanyCardStatistics
    {
        return $this->companyCardStatisticsRepository->getOneByAggregateKey(
            self::createAggregateKey(
                $company,
                $trackingDate,
                $statisticsType,
            )
        );
    }

    private static function createAggregateKey(Company $company, DateTimeInterface $trackingDate, StatisticsType $statisticsType): AggregateKeyCompanyCardStatisticsByDateAndType
    {
        $aggregateKeyCompanyCardStatisticsByDateAndType = new AggregateKeyCompanyCardStatisticsByDateAndType();
        $aggregateKeyCompanyCardStatisticsByDateAndType->company = $company;
        $aggregateKeyCompanyCardStatisticsByDateAndType->trackingDate = $trackingDate;
        $aggregateKeyCompanyCardStatisticsByDateAndType->statisticsType = $statisticsType;

        return $aggregateKeyCompanyCardStatisticsByDateAndType;
    }
}
