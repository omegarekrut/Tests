<?php

namespace Tests\Functional\Domain\WaterLevel\Command;

use App\Domain\WaterLevel\Command\UpdateGaugingStationCommand;
use App\Domain\WaterLevel\Entity\GaugingStation;
use Tests\DataFixtures\ORM\WaterLevel\LoadNovosibirskGaugingStation;
use Tests\Functional\ValidationTestCase;

/**
 * @group water-level
 */
class UpdateGaugingStationCommandValidationTest extends ValidationTestCase
{
    /** @var GaugingStation */
    private $gaugingStation;
    /** @var UpdateGaugingStationCommand */
    private $command;

    protected function setUp(): void
    {
        parent::setUp();

        $referenceRepository = $this->loadFixtures([
            LoadNovosibirskGaugingStation::class,
        ])->getReferenceRepository();

        /** @var GaugingStation $gaugingStation */
        $this->gaugingStation = $referenceRepository->getReference(LoadNovosibirskGaugingStation::REFERENCE_NAME);

        $this->command = new UpdateGaugingStationCommand($this->gaugingStation);
    }

    protected function tearDown(): void
    {
        unset($this->command);
        unset($this->gaugingStation);

        parent::tearDown();
    }

    public function testNotBlankFields(): void
    {
        $requiredFields = ['name'];

        $this->assertOnlyFieldsAreInvalid($this->command, $requiredFields, null, 'Это поле обязательно для заполнения.');
    }

    public function testInvalidTypeFields(): void
    {
        $digitFields = ['distanceFromSourceInKilometers', 'distanceToEstuaryInKilometers', 'seaLevelHeight'];

        $this->assertOnlyFieldsAreInvalid($this->command, $digitFields, 'test', 'Это значение должно быть числом.');
    }

    public function testOnMinValue(): void
    {
        $notNegativeFields = ['distanceFromSourceInKilometers', 'distanceToEstuaryInKilometers'];

        $this->assertOnlyFieldsAreInvalid($this->command, $notNegativeFields, -1, 'Расстояние не может быть меньше 0');
    }

    public function testValueIsInRange(): void
    {
        $rangedFields = ['seaLevelHeight'];

        $this->assertOnlyFieldsAreInvalid(
            $this->command,
            $rangedFields,
            -421,
            sprintf(
                'Высота над уровнем моря должна быть в пределах от %d до %d метров.',
                $this->command::SEA_LEVEL_MIN,
                $this->command::SEA_LEVEL_MAX
            )
        );

        $this->assertOnlyFieldsAreInvalid(
            $this->command,
            $rangedFields,
            8851,
            sprintf(
                'Высота над уровнем моря должна быть в пределах от %d до %d метров.',
                $this->command::SEA_LEVEL_MIN,
                $this->command::SEA_LEVEL_MAX
            )
        );
    }

    public function testValidationShouldBePassedForCorrectFilledCommand(): void
    {
        $this->command->name = 'test_station_name';
        $this->command->distanceFromSourceInKilometers = 100;
        $this->command->distanceToEstuaryInKilometers = 101;
        $this->command->seaLevelHeight = 102;

        $this->getValidator()->validate($this->command);

        $this->assertCount(0, $this->getValidator()->getLastErrors());
    }
}
