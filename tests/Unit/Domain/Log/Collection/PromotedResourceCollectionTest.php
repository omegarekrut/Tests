<?php

namespace Tests\Unit\Domain\Log\Collection;

use App\Domain\Log\Collection\PromotedResourceCollection;
use App\Domain\Log\Entity\PromotedResource;
use Tests\Unit\TestCase;

/**
 * @group log
 * @group spam-detection
 */
class PromotedResourceCollectionTest extends TestCase
{
    public function testResourceCanBeFoundInCollectionByUrl(): void
    {
        $collection = new PromotedResourceCollection([
            $this->createPromotedResourceWithUrl('http://unexpected.url'),
            $expectedResource = $this->createPromotedResourceWithUrl('http://some.com/url'),
        ]);

        $actualResource = $collection->findByUrl($expectedResource->getUrl());

        $this->assertTrue($expectedResource === $actualResource);
    }

    private function createPromotedResourceWithUrl(string $url): PromotedResource
    {
        $stub = $this->createMock(PromotedResource::class);
        $stub
            ->method('getUrl')
            ->willReturn($url);

        return $stub;
    }
}
