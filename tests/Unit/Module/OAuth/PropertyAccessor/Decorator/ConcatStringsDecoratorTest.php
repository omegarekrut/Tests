<?php

namespace Tests\Unit\Module\OAuth\PropertyAccessor\Decorator;

use App\Module\OAuth\PropertyAccessor\Decorator\ConcatStringsDecorator;
use Tests\Unit\TestCase;

/**
 * @group oauth
 * @group property-accessor
 */
class ConcatStringsDecoratorTest extends TestCase
{
    public function testDecoration(): void
    {
        $decorator = new ConcatStringsDecorator('-');
        $decoratedValue = call_user_func($decorator, ['foo', 'bar']);

        $this->assertEquals('foo-bar', $decoratedValue);
    }
}
