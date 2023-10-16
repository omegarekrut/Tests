<?php

namespace Tests\Functional\Domain\Company\Command\Rubric\Handler;

use App\Domain\Company\Command\Rubric\DeleteRubricCommand;
use App\Domain\Company\Entity\Rubric;
use Tests\DataFixtures\ORM\Company\Rubric\LoadAquaMotorcycleShopsRubric;
use Tests\Functional\TestCase;

/**
 * @group rubric
 */
class DeleteRubricHandlerTest extends TestCase
{
    public function testHandle(): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadAquaMotorcycleShopsRubric::class,
        ])->getReferenceRepository();

        /** @var Rubric $rubricToDelete */
        $rubricToDelete = $referenceRepository->getReference(LoadAquaMotorcycleShopsRubric::REFERENCE_NAME);

        $command = new DeleteRubricCommand($rubricToDelete);

        $this->getCommandBus()->handle($command);

        $this->getEntityManager()->clear();

        $rubricRepository = $this->getEntityManager()->getRepository(Rubric::class);
        $deletedCategories = $rubricRepository->find($rubricToDelete->getId());

        $this->assertEmpty($deletedCategories);
    }
}
