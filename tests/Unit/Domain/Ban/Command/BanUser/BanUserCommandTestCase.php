<?php

namespace Tests\Unit\Domain\Ban\Command\BanUser;

use App\Domain\Ban\Entity\BanUser;
use App\Domain\Ban\Repository\BanUserRepository;
use Tests\Unit\TestCase;

abstract class BanUserCommandTestCase extends TestCase
{
    protected function createBanUserRepository(array $expectedData, ?BanUser $banUser = null): BanUserRepository
    {
        $stub = $this->createMock(BanUserRepository::class);

        $stub
            ->expects($this->any())
            ->method('findActiveByUserId')
            ->willReturn($banUser)
        ;

        $stub
            ->expects($this->any())
            ->method('save')
            ->willReturnCallback(function (BanUser $banUser) use ($expectedData) {
                $this->assertEquals($expectedData['user'], $banUser->getUser());
                $this->assertEquals($expectedData['administratorUser'], $banUser->getAdministratorUser());
                $this->assertEquals($expectedData['cause'], $banUser->getCause());
                $this->assertEquals($expectedData['expiredAt'], $banUser->getExpiredAt());
            })
        ;

        return $stub;
    }
}
