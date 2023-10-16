<?php

namespace Tests\Unit\Domain\Ban\Repository\Expression\IpRange;

use App\Domain\Ban\Repository\Expression\IpRange\Ipv4Adapter;
use Tests\Unit\TestCase;

/**
 * @group ban
 */
class Ipv4AdapterTest extends TestCase
{
    public function testToNumber(): void
    {
        $adapter = new Ipv4Adapter();

        $this->assertEquals('INET_ATON(\'192.168.0.1\')', $adapter->ipToNumber('192.168.0.1'));
        $this->assertEquals('INET_ATON(field.name)', $adapter->ipToNumber('field.name'));
    }

    public function testIsVersion(): void
    {
        $adapter = new Ipv4Adapter();

        $this->assertEquals('IS_IPV4(\'192.168.0.1\')', $adapter->isVersion('192.168.0.1'));
        $this->assertEquals('IS_IPV4(field.name)', $adapter->isVersion('field.name'));
    }
}
