<?php

namespace Tests\Functional\Domain\WaterLevel\Command\Handler;

use App\Domain\WaterLevel\Command\UpdateWaterCommand;
use App\Domain\WaterLevel\Entity\Water;
use Tests\DataFixtures\ORM\WaterLevel\LoadObskoeReservoirWater;
use Tests\DataFixtures\ORM\WaterLevel\LoadObWater;
use Tests\Functional\TestCase;

/**
 * @group water-level
 */
class UpdateWaterHandlerTest extends TestCase
{
    /** @var Water */
    private $obWater;

    /** @var Water */
    private $obskoeReservoirWater;

    public function setUp(): void
    {
        parent::setUp();

        $referenceRepository = $this->loadFixtures([
            LoadObskoeReservoirWater::class,
            LoadObWater::class,
        ])->getReferenceRepository();

        $this->obWater = $referenceRepository->getReference(LoadObskoeReservoirWater::REFERENCE_NAME);
        $this->obskoeReservoirWater = $referenceRepository->getReference(LoadObWater::REFERENCE_NAME);
    }

    public function tearDown(): void
    {
        unset(
            $this->obskoeReservoirWater,
            $this->obWater
        );

        parent::tearDown();
    }

    public function testSetParentWaterAndDistanceFromSource(): void
    {
        $command = new UpdateWaterCommand($this->obskoeReservoirWater);

        $command->parentWater = $this->obWater;
        $command->distanceFromParentWaterSourceInKilometers = 222;

        $this->getCommandBus()->handle($command);

        $this->assertEquals($this->obWater, $this->obskoeReservoirWater->getParentWater());
        $this->assertEquals(
            $command->distanceFromParentWaterSourceInKilometers,
            $this->obskoeReservoirWater->getDistanceFromParentWaterSourceInKilometers($this->obWater)
        );
    }

    public function testResetDistanceFromSource(): void
    {
        $command = new UpdateWaterCommand($this->obskoeReservoirWater);

        $command->parentWater = $this->obWater;
        $command->distanceFromParentWaterSourceInKilometers = null;

        $this->getCommandBus()->handle($command);

        $this->assertEquals($this->obWater, $this->obskoeReservoirWater->getParentWater());
        $this->assertEquals(
            null,
            $this->obskoeReservoirWater->getDistanceFromParentWaterSourceInKilometers($this->obWater)
        );
    }

    public function testResetParentWater(): void
    {
        $command = new UpdateWaterCommand($this->obskoeReservoirWater);

        $command->parentWater = null;
        $command->distanceFromParentWaterSourceInKilometers = null;

        $this->getCommandBus()->handle($command);

        $this->assertEquals(null, $this->obskoeReservoirWater->getParentWater());
    }
}
