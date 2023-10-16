<?php

namespace Tests\Functional\Domain\WaterLevel\Command;

use App\Domain\WaterLevel\Command\ShowGaugingStationCommand;
use App\Domain\WaterLevel\Entity\GaugingStation;
use Tests\DataFixtures\ORM\WaterLevel\LoadBerdskGaugingStation;
use Tests\Functional\ValidationTestCase;

class ShowGaugingStationCommandTest extends ValidationTestCase
{
    /** @var GaugingStation */
    private $gaugingStation;

    protected function setUp(): void
    {
        parent::setUp();

        $referenceRepository = $this->loadFixtures([
            LoadBerdskGaugingStation::class,
        ])->getReferenceRepository();

        /** @var GaugingStation $gaugingStation */
        $this->gaugingStation = $referenceRepository->getReference(LoadBerdskGaugingStation::REFERENCE_NAME);
    }

    protected function tearDown(): void
    {
        unset($this->command);
        unset($this->gaugingStation);

        parent::tearDown();
    }

    public function testIsGaugingStationHideAlready(): void
    {
        $sendCommand = new ShowGaugingStationCommand($this->gaugingStation);

        $this->getValidator()->validate($sendCommand);

        $this->assertFieldInvalid('gaugingStationShowAlready', 'Гидропост восстановлен ранее');
    }
}
