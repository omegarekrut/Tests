<?php

namespace Tests\Unit\Domain\Ban\Command\BanUser;

use App\Auth\Visitor\Visitor;
use App\Domain\Ban\Command\BanUser\CreateBanUserCommand;
use App\Domain\Ban\Command\BanUser\Handler\CreateBanUserHandler;
use App\Domain\User\Entity\User;
use Carbon\Carbon;

/**
 * @group ban
 */
class CreateBanUserCommandHandlerTest extends BanUserCommandTestCase
{
    public function testHandle(): void
    {
        $user = $this->createMock(User::class);
        $administratorUser = $this->createMock(User::class);
        $expiredAt = Carbon::now()->addDay(5);

        $command = new CreateBanUserCommand();
        $command->user = $user;
        $command->cause = 'cause';
        $command->expiredAt = $expiredAt;

        $expectedData = [
            'user' => $user,
            'administratorUser' => $administratorUser,
            'cause' => 'cause',
            'expiredAt' => $expiredAt,
        ];

        $commandHandler = new CreateBanUserHandler(
            $this->createBanUserRepository($expectedData),
            $this->createVisitor($administratorUser)
        );

        $commandHandler->handle($command);
    }

    private function createVisitor(User $user): Visitor
    {
        $stub = $this->createMock(Visitor::class);

        $stub
            ->expects($this->never())
            ->method('getUser')
            ->willReturn($user);

        $stub
            ->method('isGuest')
            ->willReturn(true);

        return $stub;
    }
}
