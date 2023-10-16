<?php

namespace Tests\Unit\Module\ObjectLog\PropertyNormalizer;

use App\Module\ObjectLog\PropertyNormalizer\Exception\DataCanNotBeNormalizedException;
use App\Module\ObjectLog\PropertyNormalizer\LoggableCallableNormalizer;
use Closure;
use Tests\Unit\TestCase;

/**
 * @group log
 */
class LoggableCallableNormalizerTest extends TestCase
{
    private LoggableCallableNormalizer $normalizer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->normalizer = new LoggableCallableNormalizer();
    }

    protected function tearDown(): void
    {
        unset($this->normalizer);

        parent::tearDown();
    }

    public function testNormalizerCanNormalizeStaticFunction(): void
    {
        $callableValue = static function (): void {
        };

        $normalizedValue = $this->normalizer->normalizeToPlainValue($callableValue);

        $this->assertStringContainsString('callable', $normalizedValue);
    }

    public function testNormalizerCanNormalizeArrayCallableValue(): void
    {
        $callableValue = [$this, 'emptyMethodForCallableTest'];

        $normalizedValue = $this->normalizer->normalizeToPlainValue($callableValue);

        $this->assertStringContainsString('callable', $normalizedValue);
    }

    public function testNormalizerCanNormalizeClosure(): void
    {
        $callableValue = Closure::fromCallable(static function (): void {
        });

        $normalizedValue = $this->normalizer->normalizeToPlainValue($callableValue);

        $this->assertStringContainsString('callable', $normalizedValue);
    }

    public function testNormalizerCantNormalizeNotCallableValue(): void
    {
        $this->expectException(DataCanNotBeNormalizedException::class);

        $this->normalizer->normalizeToPlainValue('not callable');
    }

    public function emptyMethodForCallableTest(): void
    {
    }
}
