<?php

namespace Tests\Unit\Util\Acl;

use App\Util\Acl\CombinedAssertion;
use Tests\Unit\TestCase;
use Laminas\Permissions\Acl\Acl;

class CombinedAssertTest extends TestCase
{
    public function testUnanimousAssertTrue()
    {
        $combinedAssert = new CombinedAssertion();

        for ($i = 0; $i < 5; $i++) {
            $assertionTrue = new Assertion(true);
            $combinedAssert->addAssertion($assertionTrue);
        }

        $this->assertTrue($combinedAssert->assert(new Acl(), null, null, null));
    }

    public function testUnanimousAssertFalse()
    {
        $combinedAssert = new CombinedAssertion();
        $assertionFalse = new Assertion(false);
        $combinedAssert->addAssertion($assertionFalse);

        for ($i = 0; $i < 5; $i++) {
            $assertionTrue = new Assertion(true);
            $combinedAssert->addAssertion($assertionTrue);
        }

        $this->assertFalse($combinedAssert->assert(new Acl(), null, null, null));
    }

    public function testAtLeastOneAssertTrue()
    {
        $combinedAssert = new CombinedAssertion(CombinedAssertion::STRATEGY_AT_LEAST_ONE);
        $assertionFalse = new Assertion(true);
        $combinedAssert->addAssertion($assertionFalse);

        for ($i = 0; $i < 5; $i++) {
            $assertionTrue = new Assertion(false);
            $combinedAssert->addAssertion($assertionTrue);
        }

        $this->assertTrue($combinedAssert->assert(new Acl(), null, null, null));
    }

    public function testUnsupportedStrategy()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Стратегия foo не поддерживается. Допустимы только стратегии');

        new CombinedAssertion('foo');
    }
}
