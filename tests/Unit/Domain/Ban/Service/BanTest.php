<?php

namespace Tests\Unit\Domain\Ban\Service;

use App\Domain\Ban\Repository\BanIpRepository;
use App\Domain\Ban\Repository\BanUserRepository;
use App\Domain\Ban\Service\Ban;
use Tests\Unit\TestCase;

class BanTest extends TestCase
{
    /**
     * @dataProvider checkingIps
     */
    public function testIsBannedByIp(string $ip, bool $isBanned): void
    {
        $ban = new Ban($this->createBanIpRepository(), $this->createMock(BanUserRepository::class));

        $this->assertEquals($isBanned, $ban->isBannedByIp($ip));
    }

    public function checkingIps(): array
    {
        return [
            ['1.1.1.1', true],
            ['1.1.2.10', true],
            ['1.1.8.128', true],
            ['1.1.10.255', true],
            ['1.1.0.255', false],
            ['1.1.11.1', false],
        ];
    }

    private function createBanIpRepository(): BanIpRepository
    {
        // ip range: 1.1.1.1-1.1.10.255
        $bannedIps = [
            '1.1.1.1/32',
            '1.1.1.2/31',
            '1.1.1.4/30',
            '1.1.1.8/29',
            '1.1.1.16/28',
            '1.1.1.32/27',
            '1.1.1.64/26',
            '1.1.1.128/25',
            '1.1.2.0/23',
            '1.1.4.0/22',
            '1.1.8.0/23',
            '1.1.10.0/24'
        ];

        $stub = $this->createMock(BanIpRepository::class);
        $stub
            ->method('getBannedListIps')
            ->willReturn($bannedIps);

        return $stub;
    }
}
