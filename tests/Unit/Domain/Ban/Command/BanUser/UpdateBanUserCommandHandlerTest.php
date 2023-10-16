<?php

namespace Tests\Unit\Domain\Ban\Command\BanUser;

use App\Domain\Ban\Command\BanUser\Handler\UpdateBanUserHandler;
use App\Domain\Ban\Command\BanUser\UpdateBanUserCommand;
use App\Domain\Ban\Entity\BanUser;
use App\Domain\User\Entity\User;
use Carbon\Carbon;

/**
 * @group ban
 */
class UpdateBanUserCommandHandlerTest extends BanUserCommandTestCase
{
    public function testExecute(): void
    {
        $user = $this->createMock(User::class);
        $sourceAdministrator = $this->createMock(User::class);

        $sourceBanUser = new BanUser(
            $user,
            $sourceAdministrator,
            'old cause'
        );

        $expectedExpiredAt = Carbon::now();

        $command = new UpdateBanUserCommand($sourceBanUser);
        $command->cause = 'new cause';
        $command->expiredAt = $expectedExpiredAt;

        $expectedData = [
            'user' => $user,
            'administratorUser' => $sourceAdministrator,
            'cause' => 'new cause',
            'expiredAt' => $expectedExpiredAt,
        ];

        $handler = new UpdateBanUserHandler(
            $this->createBanUserRepository($expectedData)
        );

        $handler->handle($command);
    }
}
