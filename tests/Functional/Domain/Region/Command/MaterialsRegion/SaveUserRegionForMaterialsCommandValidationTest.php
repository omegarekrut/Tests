<?php

namespace Tests\Functional\Domain\Region\Command\MaterialsRegion;

use App\Domain\Region\Command\MaterialsRegion\SaveUserRegionForMaterialsCommand;
use Ramsey\Uuid\Uuid;
use Tests\DataFixtures\ORM\Region\Region\LoadTestRegion;
use Tests\Functional\ValidationTestCase;

class SaveUserRegionForMaterialsCommandValidationTest extends ValidationTestCase
{
    public function testValidationShouldBePassedForCorrectFilledCommand(): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadTestRegion::class,
        ])->getReferenceRepository();

        $region = $referenceRepository->getReference(LoadTestRegion::REFERENCE_NAME);

        $command = new SaveUserRegionForMaterialsCommand();
        $command->regionId = $region->getId();

        $errors = $this->getValidator()->validate($command);

        $this->assertEmpty($errors);
    }

    public function testInvalidRegionId(): void
    {
        $command = new SaveUserRegionForMaterialsCommand();
        $command->regionId = Uuid::uuid4();

        $this->getValidator()->validate($command);

        $this->assertFieldInvalid('regionId', 'Регион не найден.');
    }
}
