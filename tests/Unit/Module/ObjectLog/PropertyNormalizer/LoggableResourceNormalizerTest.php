<?php

namespace Tests\Unit\Module\ObjectLog\PropertyNormalizer;

use App\Module\ObjectLog\PropertyNormalizer\Exception\DataCanNotBeNormalizedException;
use App\Module\ObjectLog\PropertyNormalizer\LoggableResourceNormalizer;
use Tests\Unit\TestCase;

/**
 * @group log
 */
class LoggableResourceNormalizerTest extends TestCase
{
    /** @var LoggableResourceNormalizer */
    private $normalizer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->normalizer = new LoggableResourceNormalizer();
    }

    public function testNormalizerCanNormalizeOnlyResourceValue(): void
    {
        $resource = tmpfile();
        $normalizedValue = $this->normalizer->normalizeToPlainValue($resource);

        $this->assertEquals('(resource)', $normalizedValue);
    }

    public function testNormalizerCantNormalizeNotResourceValue(): void
    {
        $this->expectException(DataCanNotBeNormalizedException::class);

        $this->normalizer->normalizeToPlainValue('not resource');
    }
}
