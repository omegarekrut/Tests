<?php

namespace Tests\Functional\Domain\Landing\Command;

use App\Domain\Landing\Command\UpdateLandingCommand;
use App\Domain\Landing\Entity\Landing;
use Tests\DataFixtures\ORM\Landing\LoadTestLandings;
use Tests\Functional\ValidationTestCase;

/**
 * @group landing
 */
class UpdateLandingCommandValidationTest extends ValidationTestCase
{
    public function testValidationShouldBePassedForCorrectFilledCommand(): void
    {
        $referenceRepository = $this->loadFixtures([LoadTestLandings::class])->getReferenceRepository();
        $landing = $referenceRepository->getReference(LoadTestLandings::REFERENCE_NAME);

        /** @var Landing $landing */
        $updateLandingCommand = new UpdateLandingCommand($landing);

        $this->getValidator()->validate($updateLandingCommand);

        $this->assertEmpty($this->getValidator()->getLastErrors());
    }
}
