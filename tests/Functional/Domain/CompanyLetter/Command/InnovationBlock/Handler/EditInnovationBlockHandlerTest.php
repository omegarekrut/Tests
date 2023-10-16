<?php

namespace Tests\Functional\Domain\CompanyLetter\Command\InnovationBlock\Handler;

use App\Domain\CompanyLetter\Command\InnovationBlock\EditInnovationBlockCommand;
use App\Domain\CompanyLetter\Entity\InnovationBlock;
use App\Util\ImageStorage\Image;
use Carbon\Carbon;
use Tests\DataFixtures\ORM\CompanyLetter\LoadInnovationBlockPreviousMonth;
use Tests\Functional\TestCase;

class EditInnovationBlockHandlerTest extends TestCase
{
    public function testHandle(): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadInnovationBlockPreviousMonth::class,
        ])->getReferenceRepository();

        $innovationBlock = $referenceRepository->getReference(LoadInnovationBlockPreviousMonth::REFERENCE_NAME);
        assert($innovationBlock instanceof InnovationBlock);

        $command = new EditInnovationBlockCommand($innovationBlock);

        $command->title = 'InnovationBlock title';
        $command->data = 'InnovationBlock data';
        $command->image = new Image('image.jpg');
        $command->startAt = Carbon::now()->subDay();
        $command->finishAt = Carbon::now();

        $this->getCommandBus()->handle($command);

        $this->assertEquals($command->title, $innovationBlock->getTitle());
        $this->assertEquals($command->data, $innovationBlock->getData());
        $this->assertEquals($command->image, $innovationBlock->getImage());
        $this->assertEquals($command->startAt, $innovationBlock->getStartAt());
        $this->assertEquals($command->finishAt, $innovationBlock->getFinishAt());
    }
}
