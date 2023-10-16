<?php

namespace Tests\Functional\Domain\CompanyLetter\Command\GreetingBlock\Handler;

use App\Domain\CompanyLetter\Command\GreetingBlock\UpdateGreetingBlockCommand;
use App\Domain\CompanyLetter\Entity\GreetingBlock;
use Carbon\Carbon;
use Tests\DataFixtures\ORM\CompanyLetter\LoadGreetingBlockPreviousMonth;
use Tests\Functional\TestCase;

class UpdateGreetingBlockHandlerTest extends TestCase
{
    public function testHandle(): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadGreetingBlockPreviousMonth::class,
        ])->getReferenceRepository();

        $greetingBlock = $referenceRepository->getReference(LoadGreetingBlockPreviousMonth::REFERENCE_NAME);
        assert($greetingBlock instanceof GreetingBlock);

        $command = new UpdateGreetingBlockCommand($greetingBlock);

        $command->data = 'CompanyMailingBlock data';
        $command->startAt = Carbon::now()->subDay();
        $command->finishAt = Carbon::now();

        $this->getCommandBus()->handle($command);

        $this->assertEquals($command->data, $greetingBlock->getData());
        $this->assertEquals($command->startAt, $greetingBlock->getStartAt());
        $this->assertEquals($command->finishAt, $greetingBlock->getFinishAt());
    }
}
