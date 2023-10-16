<?php

namespace Tests\Functional\Domain\CompanyLetter\Command\InnovationBlock\Handler;

use App\Domain\CompanyLetter\Command\InnovationBlock\CreateInnovationBlockCommand;
use App\Domain\CompanyLetter\Entity\InnovationBlock;
use App\Util\ImageStorage\Image;
use Carbon\Carbon;
use Tests\Functional\TestCase;

class CreateInnovationBlockHandlerTest extends TestCase
{
    public function testHandle(): void
    {
        $command = new CreateInnovationBlockCommand();
        $command->title = 'InnovationBlock title';
        $command->data = 'InnovationBlock data';
        $command->image = new Image('image.jpg');
        $command->startAt = Carbon::now()->subDay();
        $command->finishAt = Carbon::now();

        $this->getCommandBus()->handle($command);

        $innovationBlockRepository = $this->getEntityManager()->getRepository(InnovationBlock::class);

        $innovationBlock = $innovationBlockRepository->find($command->id);
        assert($innovationBlock instanceof InnovationBlock);

        $this->assertEquals($command->id, $innovationBlock->getId());
        $this->assertEquals($command->title, $innovationBlock->getTitle());
        $this->assertEquals($command->data, $innovationBlock->getData());
        $this->assertEquals($command->image, $innovationBlock->getImage());
        $this->assertEquals($command->startAt, $innovationBlock->getStartAt());
        $this->assertEquals($command->finishAt, $innovationBlock->getFinishAt());
    }
}
