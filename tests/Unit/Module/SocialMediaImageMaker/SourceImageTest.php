<?php

namespace Tests\Unit\Module\SocialMediaImageMaker;

use App\Module\SocialMediaImageMaker\Model\SourceImage;
use PHPUnit\Framework\TestCase;

class SourceImageTest extends TestCase
{
    public function testSuccessCreate(): void
    {
        $createdSourceImage = new SourceImage('https://example.localhost/image/9999.jpg', 640, 333);

        $this->assertInstanceOf(SourceImage::class, $createdSourceImage);
        $this->assertEquals('https://example.localhost/image/9999.jpg', $createdSourceImage->url);
        $this->assertEquals(640, $createdSourceImage->width);
        $this->assertEquals(333, $createdSourceImage->height);
    }
}
