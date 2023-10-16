<?php

namespace Tests\Unit\Module\SocialMediaImageMaker;

use App\Module\SocialMediaImageMaker\Model\SocialImage;
use PHPUnit\Framework\TestCase;

class SocialImageTest extends TestCase
{
    public function testSuccessCreate(): void
    {
        $createdSocialImage = new SocialImage('image/jpg', 'content_image');

        $this->assertInstanceOf(SocialImage::class, $createdSocialImage);
        $this->assertEquals('image/jpg', $createdSocialImage->mime);
        $this->assertEquals('content_image', $createdSocialImage->content);
    }
}
