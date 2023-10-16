<?php

namespace Tests\Unit\Domain\Ban\Repository\Expression\IpRange;

use App\Domain\Ban\Repository\Expression\IpRange\Ipv6Adapter;
use Tests\Unit\TestCase;

/**
 * @group ban
 */
class Ipv6AdapterTest extends TestCase
{
    public function testToNumber(): void
    {
        $adapter = new Ipv6Adapter();

        $this->assertEquals('INET6_ATON(\'2001:db8:11a3:9d7:1f34:8a2e:7a0:765d\')', $adapter->ipToNumber('2001:db8:11a3:9d7:1f34:8a2e:7a0:765d'));
        $this->assertEquals('INET6_ATON(field.name)', $adapter->ipToNumber('field.name'));
    }

    public function testIsVersion(): void
    {
        $adapter = new Ipv6Adapter();

        $this->assertEquals('IS_IPV6(\'2001:db8:11a3:9d7:1f34:8a2e:7a0:765d\')', $adapter->isVersion('2001:db8:11a3:9d7:1f34:8a2e:7a0:765d'));
        $this->assertEquals('IS_IPV6(field.name)', $adapter->isVersion('field.name'));
    }
}
