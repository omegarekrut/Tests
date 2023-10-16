<?php

namespace Tests\Unit\Domain\Ban\Command\BanIp;

use App\Domain\Ban\Entity\BanIp;
use App\Domain\Ban\Repository\BanIpRepository;
use Tests\Unit\TestCase;

abstract class BanIpCommandTestCase extends TestCase
{
    protected function createBanIpRepository(array $expectedData, ?BanIp $activeBan = null): BanIpRepository
    {
        $stub = $this->createMock(BanIpRepository::class);
        $stub
            ->method('findAllActive')
            ->willReturn($activeBan ? [$activeBan] : [])
        ;

        $stub
            ->method('save')
            ->willReturnCallback(function (BanIp $banIp) use ($expectedData) {
                $this->assertEquals($expectedData['ipRange'], (string) $banIp->getIpRange());
                $this->assertEquals($expectedData['administratorUser'], $banIp->getAdministratorUser());
                $this->assertEquals($expectedData['cause'], $banIp->getCause());
                $this->assertEquals($expectedData['expiredAt'], $banIp->getExpiredAt());
            })
        ;

        return $stub;
    }
}
