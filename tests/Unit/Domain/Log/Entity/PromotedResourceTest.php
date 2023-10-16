<?php

namespace Tests\Unit\Domain\Log\Entity;

use App\Domain\Log\Entity\PromotedResource;
use App\Domain\Log\Entity\SpammerDetectionLog;
use Tests\Unit\TestCase;

/**
 * @group log
 * @group spam-detection
 */
class PromotedResourceTest extends TestCase
{
    public function testPromotedResourceMustContainDetailedInformationAboutResource(): void
    {
        $expectedUrl = 'http://foo.bar/test?q=quer#fragment';
        $expectedDomain = 'foo.bar';
        $expectedCanonicalUrl = '//foo.bar/test';

        $promotedResource = new PromotedResource(
            $this->createMock(SpammerDetectionLog::class),
            'http://foo.bar/test?q=quer#fragment'
        );

        $this->assertEquals($expectedUrl, $promotedResource->getUrl());
        $this->assertEquals($expectedDomain, $promotedResource->getDomain());
        $this->assertEquals($expectedCanonicalUrl, $promotedResource->getCanonicalUrl());
    }

    public function testResourceCannotBeCreatedWithEmptyUrl(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Promoted resource url must be filled.');

        new PromotedResource($this->createMock(SpammerDetectionLog::class), '');
    }
}
