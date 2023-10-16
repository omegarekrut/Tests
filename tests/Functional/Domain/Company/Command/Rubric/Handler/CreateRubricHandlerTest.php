<?php

namespace Tests\Functional\Domain\Company\Command\Rubric\Handler;

use App\Domain\Company\Command\Rubric\CreateRubricCommand;
use App\Domain\Company\Entity\Rubric;
use Ramsey\Uuid\Uuid;
use Tests\Functional\TestCase;

/**
 * @group rubric
 */
class CreateRubricHandlerTest extends TestCase
{
    public function testHandle(): void
    {
        $command = new CreateRubricCommand(Uuid::uuid4());
        $command->slug = 'slug';
        $command->name = 'name';
        $command->priority = 120;

        $this->getCommandBus()->handle($command);

        $rubricRepository = $this->getEntityManager()->getRepository(Rubric::class);

        /** @var Rubric $rubric */
        $rubric = $rubricRepository->find($command->id);

        $this->assertEquals($command->id, $rubric->getId());
        $this->assertEquals($command->slug, $rubric->getSlug());
        $this->assertEquals($command->name, $rubric->getName());
        $this->assertEquals($command->priority, $rubric->getPriority());
    }
}
