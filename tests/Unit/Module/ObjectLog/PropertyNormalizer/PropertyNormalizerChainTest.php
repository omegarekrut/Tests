<?php

namespace Tests\Unit\Module\ObjectLog\PropertyNormalizer;

use App\Module\ObjectLog\PropertyNormalizer\Exception\DataCanNotBeNormalizedException;
use App\Module\ObjectLog\PropertyNormalizer\PropertyNormalizerChain;
use App\Module\ObjectLog\PropertyNormalizer\PropertyNormalizerInterface;
use Tests\Unit\TestCase;

/**
 * @group log
 */
class PropertyNormalizerChainTest extends TestCase
{
    public function testNormalizersChainShouldFindFirstSupportedNormalizerAndNormalizeValue(): void
    {
        $normalizersChain = new PropertyNormalizerChain(
            $expectedNormalizer = $this->createSupportedNormalizer(1),
            $this->createUnsupportedNormalizer(),
            $this->createSupportedNormalizer(2)
        );

        $normalizedValue = $normalizersChain->normalizeToPlainValue('some value');

        $this->assertEquals($expectedNormalizer->normalizeToPlainValue('some value'), $normalizedValue);
    }

    public function testNormalizationShouldFailIfNormalizerNotFound(): void
    {
        $this->expectException(DataCanNotBeNormalizedException::class);

        $normalizersChain = new PropertyNormalizerChain(
            $this->createUnsupportedNormalizer()
        );

        $normalizersChain->normalizeToPlainValue('some value');
    }

    private function createSupportedNormalizer($resultValue): PropertyNormalizerInterface
    {
        $stub = $this->createMock(PropertyNormalizerInterface::class);
        $stub
            ->method('normalizeToPlainValue')
            ->willReturn($resultValue);

        return $stub;
    }

    private function createUnsupportedNormalizer(): PropertyNormalizerInterface
    {
        $stub = $this->createMock(PropertyNormalizerInterface::class);
        $stub
            ->method('normalizeToPlainValue')
            ->will($this->throwException(new DataCanNotBeNormalizedException()));

        return $stub;
    }
}
