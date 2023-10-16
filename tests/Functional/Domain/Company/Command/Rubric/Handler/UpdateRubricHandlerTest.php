<?php

namespace Tests\Functional\Domain\Company\Command\Rubric\Handler;

use App\Domain\Company\Command\Rubric\UpdateRubricCommand;
use App\Domain\Company\Entity\Rubric;
use Tests\DataFixtures\ORM\Company\Rubric\LoadAquaMotorcycleShopsRubric;
use Tests\Functional\TestCase;

/**
 * @group rubric
 */
class UpdateRubricHandlerTest extends TestCase
{
    public function testHandle(): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadAquaMotorcycleShopsRubric::class,
        ])->getReferenceRepository();

        /** @var Rubric $customRubric */
        $customRubric = $referenceRepository->getReference(LoadAquaMotorcycleShopsRubric::REFERENCE_NAME);

        $command = new UpdateRubricCommand($customRubric);
        $command->slug = 'slug';
        $command->name = 'name';

        $this->getCommandBus()->handle($command);

        $this->assertEquals($command->slug, $customRubric->getSlug());
        $this->assertEquals($command->name, $customRubric->getName());
    }
}
