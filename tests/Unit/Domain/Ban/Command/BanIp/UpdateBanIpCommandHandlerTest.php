<?php

namespace Tests\Unit\Domain\Ban\Command\BanIp;

use App\Domain\Ban\Command\BanIp\Handler\UpdateBanIpHandler;
use App\Domain\Ban\Command\BanIp\UpdateBanIpCommand;
use App\Domain\Ban\Entity\BanIp;
use App\Domain\Ban\Entity\ValueObject\IpRange;
use App\Domain\Ban\Factory\IpRangeFactory;
use App\Domain\User\Entity\User;
use Carbon\Carbon;

/**
 * @group ban
 */
class UpdateBanIpCommandHandlerTest extends BanIpCommandTestCase
{
    public function testExecute(): void
    {
        $ipRange = $this->createIpRange('127.0.0.1/32');
        $expiredAt = Carbon::now();
        $administrator = $this->createMock(User::class);
        $sourceBanIp = $this->createSourceBanIp($ipRange, $administrator);

        $command = new UpdateBanIpCommand($sourceBanIp);
        $command->cause = 'new cause';
        $command->expiredAt = $expiredAt;

        $expectedData = [
            'ipRange' => $ipRange,
            'administratorUser' => $administrator,
            'cause' => 'new cause',
            'expiredAt' => $expiredAt,
        ];

        $handler = new UpdateBanIpHandler($this->createBanIpRepository($expectedData));
        $handler->handle($command);
    }

    private function createIpRange(string $ipRangeAsString): IpRange
    {
        return (new IpRangeFactory())->createFromString($ipRangeAsString);
    }

    private function createSourceBanIp(IpRange $ipRange, User $administrator): BanIp
    {
        return new BanIp(
            $ipRange,
            $administrator,
            'old cause'
        );
    }
}
