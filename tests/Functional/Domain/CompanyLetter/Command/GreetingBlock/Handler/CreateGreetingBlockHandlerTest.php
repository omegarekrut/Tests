<?php

namespace Tests\Functional\Domain\CompanyLetter\Command\GreetingBlock\Handler;

use App\Domain\CompanyLetter\Command\GreetingBlock\CreateGreetingBlockCommand;
use App\Domain\CompanyLetter\Entity\GreetingBlock;
use Carbon\Carbon;
use Tests\Functional\TestCase;
use Ramsey\Uuid\Uuid;

class CreateGreetingBlockHandlerTest extends TestCase
{
    public function testHandle(): void
    {
        $command = new CreateGreetingBlockCommand(Uuid::uuid4());
        $command->data = 'GreetingBlock data';
        $command->startAt = Carbon::now()->subDay();
        $command->finishAt = Carbon::now();

        $this->getCommandBus()->handle($command);

        $greetingBlockRepository = $this->getEntityManager()->getRepository(GreetingBlock::class);

        $greetingBlock = $greetingBlockRepository->find($command->id);
        assert($greetingBlock instanceof GreetingBlock);

        $this->assertEquals($command->id, $greetingBlock->getId());
        $this->assertEquals($command->data, $greetingBlock->getData());
        $this->assertEquals($command->startAt, $greetingBlock->getStartAt());
        $this->assertEquals($command->finishAt, $greetingBlock->getFinishAt());
    }
}
