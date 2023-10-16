<?php

namespace Tests\Unit\Util\ImageStorage;

use App\Util\ImageStorage\Image;
use Tests\Unit\TestCase;

class ImageTest extends TestCase
{
    public function testCreate()
    {
        $file = new Image('someName__rs-90__r-42.jpg');

        $this->assertEquals('someName__rs-90__r-42', $file->getName());
        $this->assertEquals('jpg', $file->getExtension());
        $this->assertEquals('someName__rs-90__r-42.jpg', $file->getFilename());
    }
}
