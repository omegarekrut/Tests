<?php

namespace Tests\Unit\Module\OAuth\PropertyAccessor\Decorator;

use App\Module\OAuth\PropertyAccessor\Decorator\StringToDateDecorator;
use Tests\Unit\TestCase;

/**
 * @group oauth
 * @group property-accessor
 */
class StringToDateDecoratorTest extends TestCase
{
    public function testDecoration(): void
    {
        $decorator = new StringToDateDecorator();
        $decoratedValue = call_user_func($decorator, '2018-01-01');

        $this->assertInstanceOf(\DateTime::class, $decoratedValue);
        $this->assertEquals('2018-01-01', $decoratedValue->format('Y-m-d'));
    }
}
