<?php

namespace Tests\Unit\Module\ObjectLog\PropertyNormalizer;

use App\Module\ObjectLog\PropertyNormalizer\Exception\DataCanNotBeNormalizedException;
use App\Module\ObjectLog\PropertyNormalizer\LoggableNullNormalizer;
use Tests\Unit\TestCase;

/**
 * @group log
 */
class LoggableNullNormalizerTest extends TestCase
{
    /** @var LoggableNullNormalizer */
    private $normalizer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->normalizer = new LoggableNullNormalizer();
    }

    public function testNormalizerCanNormalizeOnlyNullValue(): void
    {
        $normalizedValue = $this->normalizer->normalizeToPlainValue(null);

        $this->assertTrue($normalizedValue === null);
    }

    public function testNormalizerCantNormalizeNotNullValue(): void
    {
        $this->expectException(DataCanNotBeNormalizedException::class);

        $this->normalizer->normalizeToPlainValue('not null');
    }
}
