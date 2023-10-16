<?php

namespace Tests\Functional\Domain\WaterLevel\Command;

use App\Domain\WaterLevel\Command\UpdateWaterCommand;
use App\Domain\WaterLevel\Entity\Water;
use Tests\DataFixtures\ORM\WaterLevel\LoadObskoeReservoirWater;
use Tests\DataFixtures\ORM\WaterLevel\LoadObWater;
use Tests\Functional\ValidationTestCase;

/**
 * @group water-level
 */
class UpdateWaterCommandValidationTest extends ValidationTestCase
{
    /** @var Water */
    private $obWater;
    /** @var Water */
    private $obskoeReservoirWater;
    /** @var UpdateWaterCommand */
    private $command;

    protected function setUp(): void
    {
        parent::setUp();

        $referenceRepository = $this->loadFixtures([
            LoadObWater::class,
            LoadObskoeReservoirWater::class,
        ])->getReferenceRepository();

        $this->obWater = $referenceRepository->getReference(LoadObWater::REFERENCE_NAME);
        $this->obskoeReservoirWater = $referenceRepository->getReference(LoadObskoeReservoirWater::REFERENCE_NAME);

        $this->command = new UpdateWaterCommand($this->obskoeReservoirWater);
    }

    protected function tearDown(): void
    {
        unset($this->command);
        unset($this->obskoeReservoirWater);
        unset($this->obWater);

        parent::tearDown();
    }

    public function testIfDistanceIsSetParentWaterFieldShouldNotBeBlank(): void
    {
        $this->command->parentWater = null;
        $this->command->distanceFromParentWaterSourceInKilometers = 200;

        $this->getValidator()->validate($this->command);

        $this->assertFieldInvalid(
            'parentWater',
            'Т.к. вы указали расстояние от истока родительского водного объекта необходимо указать и сам объект'
        );
    }

    public function testInvalidTypeFields(): void
    {
        $digitFields = ['distanceFromParentWaterSourceInKilometers'];

        $this->assertOnlyFieldsAreInvalid($this->command, $digitFields, 'test', 'Это значение должно быть числом');
    }

    public function testOnMinValue(): void
    {
        $notNegativeFields = ['distanceFromParentWaterSourceInKilometers'];

        $this->assertOnlyFieldsAreInvalid($this->command, $notNegativeFields, -1, 'Расстояние не может быть меньше 0');
    }

    public function testWaterShouldNotBeEqualToParentWater(): void
    {
        $this->command->parentWater = $this->obskoeReservoirWater;
        $this->command->distanceFromParentWaterSourceInKilometers = 200;

        $this->getValidator()->validate($this->command);

        $this->assertFieldInvalid(
            'water',
            'Водный объект не может быть родительским для самого себя'
        );
    }

    public function testValidationShouldBePassedForCorrectFilledCommand(): void
    {
        $this->command->parentWater = $this->obWater;
        $this->command->distanceFromParentWaterSourceInKilometers = 200;

        $this->getValidator()->validate($this->command);

        $this->assertCount(0, $this->getValidator()->getLastErrors());
    }
}
