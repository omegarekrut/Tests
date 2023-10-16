<?php

namespace Tests\Unit\Domain\Record\Tackle\Normalizer;

use App\Domain\Record\Tackle\Entity\TackleBrand;
use App\Domain\Record\Tackle\Normalizer\TackleBrandNormalizer;
use Tests\Unit\TestCase;

/**
 * @group tackleBrand
 */
class TackleBrandNormalizerTest extends TestCase
{
    public function testNormalized(): void
    {
        $normalizer = new TackleBrandNormalizer();

        $normalized = $normalizer->normalize($this->createTackleBrand('title'), 3, '/tackles/rods/spinning/');

        $this->assertCount(3, $normalized);
        $this->assertArrayHasKey('title', $normalized);
        $this->assertArrayHasKey('url', $normalized);
        $this->assertArrayHasKey('countTackles', $normalized);
    }

    private function createTackleBrand(string $title): TackleBrand
    {
        $mock = $this->createMock(TackleBrand::class);

        $mock->method('getTitle')
            ->willReturn($title);

        $mock->method('getSlug')
            ->willReturn($title);

        return $mock;
    }
}
