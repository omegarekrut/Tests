<?php

namespace Tests\Unit\Util\Cookie;

use App\Util\Cookie\Cookie;
use App\Util\Cookie\CookieCollection;
use App\Util\Cookie\CookieInterface;
use Tests\Unit\TestCase;

class CookieCollectionTest extends TestCase
{
    /**
     * @var CookieCollection
     */
    private $cookieCollection;

    protected function setUp(): void
    {
        parent::setUp();

        $this->cookieCollection = new CookieCollection();
        $this->cookieCollection->add(new Cookie('test', 111, '+5 minutes'));
    }

    public function testAdd()
    {
        $cookie = $this->cookieCollection->get('test');

        $this->assertInstanceOf(CookieInterface::class, $cookie);
        $this->assertEquals(111, $cookie->getValue());
    }

    public function testDelete()
    {
        $this->cookieCollection->delete('test');
        $cookie = $this->cookieCollection->get('test');

        $this->assertInstanceOf(CookieInterface::class, $cookie);
        $this->assertTrue($cookie->isDelete());
        $this->assertEmpty($this->cookieCollection->getCookiesForResponse());
    }
}
