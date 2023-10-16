<?php

namespace Tests\Unit\Module\WhoIs;

use App\Module\WhoIs\Exception\WhoIsServiceException;
use App\Module\WhoIs\WhoIsService;
use Carbon\Carbon;
use Exception;
use Generator;
use Iodev\Whois\Modules\Tld\TldInfo;
use Iodev\Whois\Whois;
use Tests\Unit\TestCase;

class WhoIsServiceTest extends TestCase
{
    private const DOMAIN = 'test.com';

    /**
     * @dataProvider recentlyTimeStampsDataProvider
     */
    public function testIsDomainNewReturnTrueForNewDomains(int $timestamp): void
    {
        $tldInfoMock = $this->createTldInfoMock($timestamp);
        $whoIsMock = $this->createWhoIsMock($tldInfoMock);
        $whoIsService = new WhoIsService($whoIsMock);

        $isRecentlyCreated = $whoIsService->isRecentlyCreatedDomain(self::DOMAIN);

        $this->assertTrue($isRecentlyCreated);
    }

    /**
     * @dataProvider notRecentlyTimeStampsDataProvider
     */
    public function testIsDomainNewReturnFalseForOldDomains(int $timestamp): void
    {
        $tldInfoMock = $this->createTldInfoMock($timestamp);
        $whoIsMock = $this->createWhoIsMock($tldInfoMock);
        $whoIsService = new WhoIsService($whoIsMock);

        $isRecentlyCreated = $whoIsService->isRecentlyCreatedDomain(self::DOMAIN);

        $this->assertFalse($isRecentlyCreated);
    }

    public function testIsDomainNewWillThrowWhoIsServiceExceptionIfWhoIsThrowException(): void
    {
        $expectedExceptionMessage = 'Error message';

        $whoIsMock = $this->createWhoIsMockWithException($expectedExceptionMessage);
        $whoIsService = new WhoIsService($whoIsMock);

        $this->expectException(WhoIsServiceException::class);
        $this->expectExceptionMessage($expectedExceptionMessage);

        $whoIsService->isRecentlyCreatedDomain(1);
    }

    public function recentlyTimeStampsDataProvider(): Generator
    {
        yield [Carbon::today()->subMonths(WhoIsService::APPROVE_DOMAIN_AGE_IN_MONTHS)->addMinute()->timestamp];

        yield [Carbon::today()->subMonths(WhoIsService::APPROVE_DOMAIN_AGE_IN_MONTHS)->addWeek()->timestamp];

        yield [Carbon::today()->subMonths(WhoIsService::APPROVE_DOMAIN_AGE_IN_MONTHS)->addMonth()->timestamp];
    }

    public function notRecentlyTimeStampsDataProvider(): Generator
    {
        yield [Carbon::today()->subMonths(WhoIsService::APPROVE_DOMAIN_AGE_IN_MONTHS)->subSecond()->timestamp];

        yield [Carbon::today()->subMonths(WhoIsService::APPROVE_DOMAIN_AGE_IN_MONTHS)->subYear()->timestamp];

        yield [Carbon::today()->subMonths(WhoIsService::APPROVE_DOMAIN_AGE_IN_MONTHS)->timestamp];
    }

    private function createWhoIsMock(TldInfo $tldInfo): Whois
    {
        $whoIsMock = $this->createMock(Whois::class);
        $whoIsMock
            ->expects($this->once())
            ->method('loadDomainInfo')
            ->willReturn($tldInfo);

        return $whoIsMock;
    }

    private function createTldInfoMock(int $timestamp): TldInfo
    {
        $tldInfoMock = $this->createMock(TldInfo::class);
        $tldInfoMock->creationDate = $timestamp;

        return $tldInfoMock;
    }

    private function createWhoIsMockWithException(string $exceptionMessage): Whois
    {
        $whoIsMock = $this->createMock(Whois::class);
        $whoIsMock
            ->expects($this->once())
            ->method('loadDomainInfo')
            ->willThrowException(new Exception($exceptionMessage));

        return $whoIsMock;
    }
}
