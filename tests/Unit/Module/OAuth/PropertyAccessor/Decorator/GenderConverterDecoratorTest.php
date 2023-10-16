<?php

namespace Tests\Unit\Module\OAuth\PropertyAccessor\Decorator;

use App\Module\OAuth\Entity\ValueObject\Gender;
use App\Module\OAuth\PropertyAccessor\Decorator\GenderConverterDecorator;
use Tests\Unit\TestCase;

/**
 * @group oauth
 * @group property-accessor
 */
class GenderConverterDecoratorTest extends TestCase
{
    public function testDecoration(): void
    {
        $decorator = new GenderConverterDecorator('FEMALE', 'MALE');
        $decoratedValue = call_user_func($decorator, 'MALE');

        $this->assertInstanceOf(Gender::class, $decoratedValue);
        $this->assertEquals(Gender::MALE, (string) $decoratedValue);
    }
}
