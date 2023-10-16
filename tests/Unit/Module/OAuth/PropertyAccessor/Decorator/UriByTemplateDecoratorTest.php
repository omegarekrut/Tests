<?php

namespace Tests\Unit\Module\OAuth\PropertyAccessor\Decorator;

use App\Module\OAuth\PropertyAccessor\Decorator\UriByTemplateDecorator;
use Psr\Http\Message\UriInterface;
use Tests\Unit\TestCase;

/**
 * @group oauth
 * @group property-accessor
 */
class UriByTemplateDecoratorTest extends TestCase
{
    public function testDecoration(): void
    {
        $decorator = new UriByTemplateDecorator('http://template.%s');
        $decoratedValue = call_user_func($decorator, 'com');

        $this->assertInstanceOf(UriInterface::class, $decoratedValue);
        $this->assertEquals('http://template.com', (string) $decoratedValue);
    }
}
