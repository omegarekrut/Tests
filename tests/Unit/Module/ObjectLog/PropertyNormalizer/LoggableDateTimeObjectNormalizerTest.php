<?php

namespace Tests\Unit\Module\ObjectLog\PropertyNormalizer;

use App\Module\ObjectLog\PropertyNormalizer\Exception\DataCanNotBeNormalizedException;
use App\Module\ObjectLog\PropertyNormalizer\LoggableDateTimeObjectNormalizer;
use Tests\Unit\TestCase;

/**
 * @group log
 */
class LoggableDateTimeObjectNormalizerTest extends TestCase
{
    /** @var LoggableDateTimeObjectNormalizer */
    private $normalizer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->normalizer = new LoggableDateTimeObjectNormalizer();
    }

    public function testNormalizerCanNormalizeDateTimeValue(): void
    {
        $now = new \DateTime();
        $normalizedValue = $this->normalizer->normalizeToPlainValue($now);

        $expectedNormalizedDateTime = '(DateTime) '.$now->format('Y-m-d H:i:s');

        $this->assertEquals($expectedNormalizedDateTime, $normalizedValue);
    }

    public function testNormalizerCantNormalizeNotDateTimeValue(): void
    {
        $this->expectException(DataCanNotBeNormalizedException::class);

        $this->normalizer->normalizeToPlainValue($this);
    }
}
