<?php

namespace Tests\Unit\Util\ImageStorage;

use App\Util\ImageStorage\Image;
use App\Util\ImageStorage\ImageTransformer;
use App\Util\ImageStorage\ImageTransformerFactory;
use Tests\Unit\TestCase;

class ImageTransformerFactoryTest extends TestCase
{
    public function testCreate()
    {
        $factory = new ImageTransformerFactory('http://image.com', 'key', 'siteId');

        $file = $factory->create(new Image('filename__r-42.jpg'));

        $this->assertInstanceOf(ImageTransformer::class, $file);
        $this->assertEquals('http://image.com/siteId/filename.jpg?hash=38be4232', (string) $file);
    }
}
