<?php

namespace Tests\Unit\Domain\User\Command\Rating;

use App\Bridge\Queue\UserRatingRecalculationQueueCommandFactory;
use App\Domain\User\Command\Rating\Handler\RecalculateAllUserRatingsHandler;
use App\Domain\User\Command\Rating\RecalculateAllUserRatingsCommand;
use App\Domain\User\Command\Rating\RecalculateUserActivityRatingCommand;
use App\Domain\User\Command\Rating\RecalculateUserGlobalRatingCommand;
use Tests\Unit\Mock\CommandBusMock;
use Tests\Unit\TestCase;

class RecalculateAllUserRatingsHandlerTest extends TestCase
{
    public function testHandle(): void
    {
        $userRatingRecalculateQueueCommandFactory = new UserRatingRecalculationQueueCommandFactory();

        $commandBus = new CommandBusMock();

        $handler = new RecalculateAllUserRatingsHandler($commandBus, $userRatingRecalculateQueueCommandFactory);

        $command = new RecalculateAllUserRatingsCommand();
        $command->userId = 42;

        $handler->handle($command);

        $handledCommands = $commandBus->getAllHandledCommands();

        $this->assertCount(2, $handledCommands);

        $this->assertTrue($commandBus->isHandled(RecalculateUserActivityRatingCommand::class));
        $this->assertTrue($commandBus->isHandled(RecalculateUserGlobalRatingCommand::class));
    }
}
