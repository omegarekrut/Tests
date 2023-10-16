<?php

namespace Tests\Unit\Auth\Visitor\Service;

use App\Module\Geo\TransferObject\LocationDTO;
use App\Service\ClientIp;
use Tests\Unit\TestCase;

abstract class LocationServicesTestService extends TestCase
{
    protected function getClientIp(string $ip = '127.0.0.1'): ClientIp
    {
        $mock = $this->createMock(ClientIp::class);

        $mock->method('getIp')
            ->willReturn($ip);

        return $mock;
    }

    protected function createExpectedCookieValueFromLocation(LocationDTO $locationDTO): string
    {
        return sprintf(
            '%s/%s/%s/%s,%s',
            $this->getClientIp()->getIp(),
            $locationDTO->region->getId(),
            $locationDTO->city,
            $locationDTO->coordinates->getLatitude(),
            $locationDTO->coordinates->getLongitude()
        );
    }
}
