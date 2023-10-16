<?php

namespace Tests\Functional\Domain\WaterLevel\Command\Handler;

use App\Domain\WaterLevel\Command\Handler\UpdateGaugingStationRecordsHandler;
use App\Domain\WaterLevel\Command\UpdateGaugingStationRecordsCommand;
use App\Domain\WaterLevel\Entity\GaugingStationProvider;
use App\Domain\WaterLevel\Entity\GaugingStationProviderRecord;
use App\Domain\WaterLevel\Entity\ValueObject\GaugingStationRecordsProviderKey;
use App\Domain\WaterLevel\Entity\ValueObject\WaterType;
use App\Domain\WaterLevel\Repository\GaugingStationProviderRepository;
use App\Domain\WaterLevel\Repository\GaugingStationRecordRepository;
use App\Module\GaugingStationRecordsProvider\GaugingStationRecordsProviderInterface;
use App\Module\GaugingStationRecordsProvider\TransferObject\GaugingStationProvider as ProvidedGaugingStation;
use App\Module\GaugingStationRecordsProvider\TransferObject\GaugingStationRecord as ProvidedGaugingStationRecord;
use App\Module\GaugingStationRecordsProvider\TransferObject\GeographicalPosition;
use App\Module\GaugingStationRecordsProvider\TransferObject\Water as ProvidedWater;
use App\Module\GaugingStationRecordsProvider\TransferObject\WaterType as ProvidedWaterType;
use App\Util\Coordinates\Coordinates;
use Psr\Log\LoggerInterface;
use Tests\DataFixtures\ORM\WaterLevel\LoadEsimoGaugingStationProviderForNovosibirskGaugingStation;
use Tests\Functional\TestCase;
use Exception;

/**
 * @group water-level
 * @group gauging-station-records-provider
 */
class UpdateGaugingStationRecordsHandlerTest extends TestCase
{
    public function testHandleWithExistingProvider(): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadEsimoGaugingStationProviderForNovosibirskGaugingStation::class,
        ])->getReferenceRepository();

        $gaugingStationProvider = $referenceRepository->getReference(LoadEsimoGaugingStationProviderForNovosibirskGaugingStation::REFERENCE_NAME);
        assert($gaugingStationProvider instanceof GaugingStationProvider);

        $providedRecord = $this->createProvidedGaugingStationRecord(
            $gaugingStationProvider->getWaterName(),
            WaterType::river(),
            $gaugingStationProvider->getExternalIdentifier()->getId(),
            $gaugingStationProvider->getExternalIdentifier()->getName(),
        );
        $recordsProvider = $this->createGaugingStationRecordsProvider($providedRecord, $gaugingStationProvider->getExternalIdentifier()->getKey());
        $commandHandler = $this->createUpdateGaugingStationRecordsHandler([$recordsProvider]);

        $commandHandler->handle(new UpdateGaugingStationRecordsCommand());

        $recordRepository = $this->getEntityManager()->getRepository(GaugingStationProviderRecord::class);

        $lastRecord = $recordRepository->findOneBy(['recordedAt' => $providedRecord->getRecordedAt()]);
        assert($lastRecord instanceof GaugingStationProviderRecord);

        $this->assertEquals($providedRecord->getTemperature(), $lastRecord->getTemperature());
        $this->assertEquals($providedRecord->getWaterLevel(), $lastRecord->getWaterLevel());

        $this->assertEquals($gaugingStationProvider, $lastRecord->getGaugingStationProvider());
    }

    public function testHandleWithNonExistingStation(): void
    {
        $expectedWaterName = 'Волга';

        $providedRecord = $this->createProvidedGaugingStationRecord($expectedWaterName, WaterType::river(), '123467', 'Дубровино');
        $recordsProvider = $this->createGaugingStationRecordsProvider($providedRecord, GaugingStationRecordsProviderKey::esimo());
        $commandHandler = $this->createUpdateGaugingStationRecordsHandler([$recordsProvider]);

        $commandHandler->handle(new UpdateGaugingStationRecordsCommand());

        $recordRepository = $this->getEntityManager()->getRepository(GaugingStationProviderRecord::class);

        $lastRecord = $recordRepository->findOneBy(['recordedAt' => $providedRecord->getRecordedAt()]);
        assert($lastRecord instanceof GaugingStationProviderRecord);

        $this->assertEquals($expectedWaterName, $lastRecord->getGaugingStationProvider()->getWaterName());
        $this->assertEquals($providedRecord->getGaugingStationProvider()->getId(), $lastRecord->getGaugingStationProvider()->getExternalIdentifier()->getId());
        $this->assertEquals($providedRecord->getTemperature(), $lastRecord->getTemperature());
        $this->assertEquals($providedRecord->getWaterLevel(), $lastRecord->getWaterLevel());
    }

    public function testHandleWithNonExistingWater(): void
    {
        $providedRecord = $this->createProvidedGaugingStationRecord('Обское водохранилище', WaterType::reservoir(), '123456', 'Спирино');
        $recordsProvider = $this->createGaugingStationRecordsProvider($providedRecord, GaugingStationRecordsProviderKey::esimo());
        $commandHandler = $this->createUpdateGaugingStationRecordsHandler([$recordsProvider]);

        $commandHandler->handle(new UpdateGaugingStationRecordsCommand());

        $recordRepository = $this->getEntityManager()->getRepository(GaugingStationProviderRecord::class);

        $lastRecord = $recordRepository->findOneBy(['recordedAt' => $providedRecord->getRecordedAt()]);
        assert($lastRecord instanceof GaugingStationProviderRecord);

        $this->assertEquals($providedRecord->getWater()->getName(), $lastRecord->getGaugingStationProvider()->getWaterName());
        $this->assertEquals($providedRecord->getTemperature(), $lastRecord->getTemperature());
        $this->assertEquals($providedRecord->getWaterLevel(), $lastRecord->getWaterLevel());
    }

    public function testFirstProviderThrowException(): void
    {
        $providedRecord = $this->createProvidedGaugingStationRecord('Обское водохранилище', WaterType::reservoir(), '123456', 'Спирино');
        $brokenProvider = $this->createBrokenGaugingStationRecordsProvider();
        $workingProvider = $this->createGaugingStationRecordsProvider($providedRecord, GaugingStationRecordsProviderKey::esimo());

        $providers = [
            $brokenProvider,
            $workingProvider,
        ];

        $commandHandler = $this->createUpdateGaugingStationRecordsHandler($providers);

        $commandHandler->handle(new UpdateGaugingStationRecordsCommand());

        $this->assertLastRecordEqualsProvided($providedRecord);
    }

    private function assertLastRecordEqualsProvided(ProvidedGaugingStationRecord $providedRecord): void
    {
        $recordRepository = $this->getEntityManager()->getRepository(GaugingStationProviderRecord::class);

        $lastRecord = $recordRepository->findOneBy(['recordedAt' => $providedRecord->getRecordedAt()]);
        assert($lastRecord instanceof GaugingStationProviderRecord);

        $this->assertEquals($providedRecord->getWater()->getName(), $lastRecord->getGaugingStationProvider()->getWaterName());
        $this->assertEquals($providedRecord->getTemperature(), $lastRecord->getTemperature());
        $this->assertEquals($providedRecord->getWaterLevel(), $lastRecord->getWaterLevel());
    }

    /**
     * @param GaugingStationRecordsProviderInterface[] $gaugingStationRecordsProviders
     */
    private function createUpdateGaugingStationRecordsHandler(array $gaugingStationRecordsProviders): UpdateGaugingStationRecordsHandler
    {
        return new UpdateGaugingStationRecordsHandler(
            $this->getCommandBus(),
            $gaugingStationRecordsProviders,
            $this->getContainer()->get(GaugingStationProviderRepository::class),
            $this->getContainer()->get(GaugingStationRecordRepository::class),
            $this->createMock(LoggerInterface::class)
        );
    }

    private function createBrokenGaugingStationRecordsProvider(): GaugingStationRecordsProviderInterface
    {
        $stub = $this->createMock(GaugingStationRecordsProviderInterface::class);

        $stub->method('provide')
            ->willThrowException(new Exception('Error'));

        return $stub;
    }

    private function createGaugingStationRecordsProvider(ProvidedGaugingStationRecord $gaugingStationRecord, GaugingStationRecordsProviderKey $providerKey): GaugingStationRecordsProviderInterface
    {
        $stub = $this->createMock(GaugingStationRecordsProviderInterface::class);

        $stub->method('provide')
            ->willReturn([$gaugingStationRecord]);

        $stub->method('getProviderKey')
            ->willReturn($providerKey);

        return $stub;
    }

    private function createProvidedGaugingStationRecord(
        string $waterName,
        WaterType $waterType,
        string $stationId,
        string $stationName
    ): ProvidedGaugingStationRecord {

        $faker = $this->getFaker();

        $providedGaugingStation = new ProvidedGaugingStation(
            $stationId,
            $stationName,
            new GeographicalPosition(
                new Coordinates($faker->randomFloat(2, 40, 60), $faker->randomFloat(2, 40, 60)),
                $faker->randomFloat(2, 100, 1000),
                $faker->randomFloat(2, 100, 1000),
                $faker->randomDigit
            )
        );

        switch ($waterType) {
            case WaterType::lake():
                $providedWaterType = ProvidedWaterType::lake();

                break;
            case WaterType::river():
                $providedWaterType = ProvidedWaterType::river();

                break;
            case WaterType::reservoir():
                $providedWaterType = ProvidedWaterType::reservoir();

                break;
        }

        $providedWater = new ProvidedWater($waterName, $providedWaterType);

        return new ProvidedGaugingStationRecord(
            $providedGaugingStation,
            $providedWater,
            $faker->randomDigit,
            $faker->randomFloat(1, 10, 30),
            $faker->dateTime
        );
    }
}
