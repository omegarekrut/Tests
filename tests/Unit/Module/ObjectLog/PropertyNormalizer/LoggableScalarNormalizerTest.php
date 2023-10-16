<?php

namespace Tests\Unit\Module\ObjectLog\PropertyNormalizer;

use App\Module\ObjectLog\PropertyNormalizer\Exception\DataCanNotBeNormalizedException;
use App\Module\ObjectLog\PropertyNormalizer\LoggableScalarNormalizer;
use Tests\Unit\TestCase;

/**
 * @group log
 */
class LoggableScalarNormalizerTest extends TestCase
{
    /** @var LoggableScalarNormalizer */
    private $normalizer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->normalizer = new LoggableScalarNormalizer();
    }

    /**
     * @dataProvider getScalarValues
     */
    public function testScalarValueImmutableShouldStayAfterNormalization($scalarValue): void
    {
        $normalizedValue = $this->normalizer->normalizeToPlainValue($scalarValue);

        $this->assertTrue($scalarValue === $normalizedValue);
    }

    public function getScalarValues(): \Generator
    {
        yield [
            true,
        ];

        yield [
            1,
        ];

        yield [
            1.1,
        ];

        yield [
            1.1,
        ];

        yield [
            'string',
        ];
    }

    public function testNormalizerCantNormalizeNotScalarValue(): void
    {
        $this->expectException(DataCanNotBeNormalizedException::class);

        $this->normalizer->normalizeToPlainValue($this);
    }
}
