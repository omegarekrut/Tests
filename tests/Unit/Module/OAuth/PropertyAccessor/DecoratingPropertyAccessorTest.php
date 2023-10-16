<?php

namespace Tests\Unit\Module\OAuth\PropertyAccessor;

use App\Module\OAuth\PropertyAccessor\DecoratingPropertyAccessor;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Tests\Unit\TestCase;

/**
 * @group oauth
 * @group property-accessor
 */
class DecoratingPropertyAccessorTest extends TestCase
{
    private const EXPECTED_DATA = [
        'key' => 'value',
        'key2' => 'value2',
    ];

    /** @var DecoratingPropertyAccessor */
    private $propertyAccessor;

    /** @var callable */
    private $exclamationMarkDecorator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->propertyAccessor = new DecoratingPropertyAccessor(PropertyAccess::createPropertyAccessor());
        $this->exclamationMarkDecorator = function (string $value) {
            return $value.'!';
        };
    }

    public function testDecorateValue(): void
    {
        $value = $this->propertyAccessor->getValue(self::EXPECTED_DATA, '[key]', $this->exclamationMarkDecorator);

        $this->assertEquals('value!', $value);
    }

    public function testEmptyValue(): void
    {
        $value = $this->propertyAccessor->getValue(self::EXPECTED_DATA, 'not-exists-key', $this->exclamationMarkDecorator);

        $this->assertEmpty($value);
    }

    public function testSeveralValues(): void
    {
        $value = $this->propertyAccessor->getValue(self::EXPECTED_DATA, ['[key]', '[key2]']);

        $this->assertEquals([
            'value',
            'value2',
        ], $value);
    }
}
