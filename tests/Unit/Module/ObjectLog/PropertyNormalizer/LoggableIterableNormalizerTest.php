<?php

namespace Tests\Unit\Module\ObjectLog\PropertyNormalizer;

use App\Module\ObjectLog\PropertyNormalizer\Exception\DataCanNotBeNormalizedException;
use App\Module\ObjectLog\PropertyNormalizer\LoggableIterableNormalizer;
use ArrayIterator;
use Generator;
use Tests\Unit\TestCase;

/**
 * @group log
 */
class LoggableIterableNormalizerTest extends TestCase
{
    private LoggableIterableNormalizer $normalizer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->normalizer = new LoggableIterableNormalizer();
    }

    protected function tearDown(): void
    {
        unset($this->normalizer);

        parent::tearDown();
    }

    public function testNormalizerCanNormalizeGenerator(): void
    {
        $iterableValue = static function (): Generator {
            yield 1;
        };

        $normalizedValue = $this->normalizer->normalizeToPlainValue($iterableValue());

        $this->assertStringContainsString('iterable', $normalizedValue);
    }

    public function testNormalizerCanNormalizeArray(): void
    {
        $iterableValue = ['plain' => 'array'];

        $normalizedValue = $this->normalizer->normalizeToPlainValue($iterableValue);

        $this->assertStringContainsString('iterable', $normalizedValue);
    }

    public function testNormalizerCanNormalizeArrayIterator(): void
    {
        $iterableValue = new ArrayIterator(['some value']);

        $normalizedValue = $this->normalizer->normalizeToPlainValue($iterableValue);

        $this->assertStringContainsString('iterable', $normalizedValue);
    }

    public function testNormalizerCantNormalizeNotIterableValue(): void
    {
        $this->expectException(DataCanNotBeNormalizedException::class);

        $this->normalizer->normalizeToPlainValue('not iterable');
    }

    public function testNormalizedCountableValuesShouldHaveCount(): void
    {
        $twoItems = [1, 2];
        $normalizedValue = $this->normalizer->normalizeToPlainValue($twoItems);

        $this->assertEquals('(iterable:2)', $normalizedValue);
    }
}
