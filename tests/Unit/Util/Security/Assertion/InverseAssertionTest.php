<?php

namespace Tests\Unit\Util\Security\Assertion;

use App\Util\Security\Assertion\InverseDecorator as DecoratorInverseAssertion;
use Tests\Unit\TestCase;
use Laminas\Permissions\Acl\Acl;
use Laminas\Permissions\Acl\Assertion\AssertionInterface;

class InverseAssertionTest extends TestCase
{
    public function testInverseAssertWithTrueAssert(): void
    {
        $trueAssertion = $this->getAssertionMock(true);

        $acl = new Acl();
        $sourceResultAssert = $trueAssertion->assert($acl);

        $inverseAssertion = new DecoratorInverseAssertion($trueAssertion);
        $actualAssertResult = $inverseAssertion->assert($acl);

        $this->assertNotEquals($sourceResultAssert, $actualAssertResult);
    }

    public function testInverseAssertWithFalseAssert(): void
    {
        $falseAssertion = $this->getAssertionMock(false);

        $acl = new Acl();
        $sourceResultAssert = $falseAssertion->assert($acl);

        $inverseAssertion = new DecoratorInverseAssertion($falseAssertion);
        $actualAssertResult = $inverseAssertion->assert($acl);

        $this->assertNotEquals($sourceResultAssert, $actualAssertResult);
    }

    private function getAssertionMock(bool $resultAssert): AssertionInterface
    {
        $stub = $this->createMock(AssertionInterface::class);
        $stub->method('assert')->willReturn($resultAssert);

        return $stub;
    }
}
