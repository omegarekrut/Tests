<?php

namespace Tests\Unit\Domain\User\Command\Rating;

use App\Domain\User\Collection\UserCollection;
use App\Domain\User\Command\Rating\Handler\RecalculateUsersRatingsForPreviousDayEventsHandler;
use App\Domain\User\Command\Rating\RecalculateAllUserRatingsCommand;
use App\Domain\User\Command\Rating\RecalculateUsersRatingForPreviousDayEventsCommand;
use App\Domain\User\Entity\User;
use App\Domain\User\Service\LikedService;
use Psr\Log\LoggerInterface;
use Tests\Unit\Mock\CommandBusMock;
use Tests\Unit\TestCase;

class RecalculateUsersRatingsForPreviousDayEventsHandlerTest extends TestCase
{
    public function testRecalculateUsersRatings(): void
    {
        $users = [
            $this->getUser(1),
            $this->getUser(2),
        ];

        $commandBus = new CommandBusMock();

        $handler = new RecalculateUsersRatingsForPreviousDayEventsHandler(
            $commandBus,
            $this->getLikedService($users),
            $this->createMock(LoggerInterface::class)
        );
        $handler->handle(new RecalculateUsersRatingForPreviousDayEventsCommand());

        $calledCommands = $commandBus->getAllHandledCommands();

        $this->assertCount(2, $calledCommands);

        foreach ($calledCommands as $calledCommand) {
            $this->assertInstanceOf(RecalculateAllUserRatingsCommand::class, $calledCommand);
            $this->assertNotEmpty(
                array_filter(
                    $users,
                    static fn (User $user): bool => $user->getId() === $calledCommand->userId
                )
            );
        }
    }

    /**
     * @param User[] $users
     */
    private function getLikedService(array $users): LikedService
    {
        $likedService = $this->createMock(LikedService::class);
        $likedService->method('getUserAreRatedForDay')
            ->willReturn(new UserCollection($users));

        return $likedService;
    }

    private function getUser(int $userId): User
    {
        $user = $this->createMock(User::class);
        $user->method('getId')
            ->willReturn($userId);

        return $user;
    }
}
