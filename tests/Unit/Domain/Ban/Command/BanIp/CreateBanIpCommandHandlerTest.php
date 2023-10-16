<?php

namespace Tests\Unit\Domain\Ban\Command\BanIp;

use App\Auth\Visitor\Visitor;
use App\Domain\Ban\Command\BanIp\CreateBanIpCommand;
use App\Domain\Ban\Command\BanIp\Handler\CreateBanIpHandler;
use App\Domain\Ban\Factory\IpRangeFactory;
use App\Domain\User\Entity\User;
use Carbon\Carbon;

/**
 * @group ban
 */
class CreateBanIpCommandHandlerTest extends BanIpCommandTestCase
{
    public function testHandle(): void
    {
        $ipRange = '127.0.0.1';
        $administratorUser = $this->createMock(User::class);
        $expiredAt = Carbon::now()->addDay(5);

        $command = new CreateBanIpCommand();
        $command->ipRange = $ipRange;
        $command->cause = 'cause';
        $command->expiredAt = $expiredAt;

        $expectedData = [
            'ipRange' => $ipRange.'/32',
            'administratorUser' => $administratorUser,
            'cause' => 'cause',
            'expiredAt' => $expiredAt,
        ];

        $commandHandler = new CreateBanIpHandler(
            $this->createBanIpRepository($expectedData),
            $this->createVisitor($administratorUser),
            new IpRangeFactory()
        );

        $commandHandler->handle($command);
    }

    private function createVisitor(User $user): Visitor
    {
        $visitor = $this->createMock(Visitor::class);

        $visitor
            ->method('getUser')
            ->willReturn($user);

        return $visitor;
    }
}
