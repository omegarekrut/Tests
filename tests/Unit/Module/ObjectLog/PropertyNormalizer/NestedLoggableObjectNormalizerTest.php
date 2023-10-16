<?php

namespace Tests\Unit\Module\ObjectLog\PropertyNormalizer;

use App\Module\ObjectLog\PropertyNormalizer\Exception\DataCanNotBeNormalizedException;
use App\Module\ObjectLog\PropertyNormalizer\NestedLoggableObjectNormalizer;
use Tests\Unit\TestCase;

class NestedLoggableObjectNormalizerTest extends TestCase
{
    /** @var NestedLoggableObjectNormalizer */
    private $normalizer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->normalizer = new NestedLoggableObjectNormalizer();
    }

    public function testObjectMustBeNormalizedToObjectClassName(): void
    {
        $normalizedValue = $this->normalizer->normalizeToPlainValue($this);

        $this->assertEquals('('.static::class.')', $normalizedValue);
    }

    public function testNormalizerCantNormalizeNotObjectValue(): void
    {
        $this->expectException(DataCanNotBeNormalizedException::class);

        $this->normalizer->normalizeToPlainValue('not object');
    }
}
