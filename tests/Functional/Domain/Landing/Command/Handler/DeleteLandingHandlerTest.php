<?php

namespace Tests\Functional\Domain\Landing\Command\Handler;

use App\Domain\Landing\Command\DeleteLandingCommand;
use App\Domain\Landing\Entity\Landing;
use Tests\DataFixtures\ORM\Landing\LoadTestLandings;
use Tests\Functional\TestCase;

/**
 * @group landing
 */
class DeleteLandingHandlerTest extends TestCase
{
    public function testLandingsCanBeDeleted(): void
    {
        $referenceRepository = $this->loadFixtures([LoadTestLandings::class])->getReferenceRepository();

        /** @var Landing $landing */
        $landing = $referenceRepository->getReference(LoadTestLandings::REFERENCE_NAME);

        $landingSlug = $landing->getSlug();

        $deleteLandingCommand = new DeleteLandingCommand($landing);
        $this->getCommandBus()->handle($deleteLandingCommand);

        $landingRepository = $this->getEntityManager()->getRepository(Landing::class);
        $deletedLanding = $landingRepository->findBySlug($landingSlug);

        $this->assertNull($deletedLanding);
    }
}
