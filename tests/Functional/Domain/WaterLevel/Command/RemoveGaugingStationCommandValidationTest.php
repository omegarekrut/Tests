<?php

namespace Tests\Functional\Domain\WaterLevel\Command;

use App\Domain\WaterLevel\Command\RemoveGaugingStationCommand;
use App\Domain\WaterLevel\Entity\GaugingStation;
use Tests\DataFixtures\ORM\WaterLevel\LoadGaugingStationWithoutProviders;
use Tests\DataFixtures\ORM\WaterLevel\LoadNovosibirskGaugingStation;
use Tests\Functional\ValidationTestCase;

class RemoveGaugingStationCommandValidationTest extends ValidationTestCase
{
    private GaugingStation $gaugingStationWithProviders;
    private GaugingStation $gaugingStationWithoutProviders;

    protected function setUp(): void
    {
        parent::setUp();

        $referenceRepository = $this->loadFixtures([
            LoadNovosibirskGaugingStation::class,
            LoadGaugingStationWithoutProviders::class,
        ])->getReferenceRepository();

        $this->gaugingStationWithProviders = $referenceRepository->getReference(LoadNovosibirskGaugingStation::REFERENCE_NAME);
        $this->gaugingStationWithoutProviders = $referenceRepository->getReference(LoadGaugingStationWithoutProviders::REFERENCE_NAME);
    }

    public function testGaugingStationFieldWithProviders(): void
    {
        $requiredFields = ['gaugingStationId'];
        $command = new RemoveGaugingStationCommand($this->gaugingStationWithProviders->getId());

        $this->assertOnlyFieldsAreInvalid(
            $command,
            $requiredFields,
            $this->gaugingStationWithProviders->getId(),
            sprintf('Чтобы удалить гидропост %s отвяжите от него всех поставщиков.', $this->gaugingStationWithProviders->getName())
        );
    }

    public function testGaugingStationFieldWithoutProviders(): void
    {
        $command = new RemoveGaugingStationCommand($this->gaugingStationWithoutProviders->getId());

        $this->getValidator()->validate($command);

        $this->assertCount(0, $this->getValidator()->getLastErrors());
    }
}
