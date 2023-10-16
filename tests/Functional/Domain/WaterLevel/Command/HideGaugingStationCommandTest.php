<?php

namespace Tests\Functional\Domain\WaterLevel\Command;

use App\Domain\WaterLevel\Command\HideGaugingStationCommand;
use App\Domain\WaterLevel\Entity\GaugingStation;
use Tests\DataFixtures\ORM\WaterLevel\LoadHideBerdskGaugingStation;
use Tests\Functional\ValidationTestCase;

/**
 * @group water-level
 */
class HideGaugingStationCommandTest extends ValidationTestCase
{
    /** @var GaugingStation */
    private $gaugingStation;

    protected function setUp(): void
    {
        parent::setUp();

        $referenceRepository = $this->loadFixtures([
            LoadHideBerdskGaugingStation::class,
        ])->getReferenceRepository();

        /** @var GaugingStation $gaugingStation */
        $this->gaugingStation = $referenceRepository->getReference(LoadHideBerdskGaugingStation::REFERENCE_NAME);
    }

    protected function tearDown(): void
    {
        unset($this->command);
        unset($this->gaugingStation);

        parent::tearDown();
    }

    public function testIsGaugingStationHideAlready(): void
    {
        $sendCommand = new HideGaugingStationCommand($this->gaugingStation);

        $this->getValidator()->validate($sendCommand);

        $this->assertFieldInvalid('gaugingStationHideAlready', 'Гидропост скрыт ранее');
    }
}
