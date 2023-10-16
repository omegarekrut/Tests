<?php

namespace Tests\Unit\Util\ImageStorage\Normalizer;

use App\Util\ImageStorage\Image;
use App\Util\ImageStorage\ImageTransformerFactory;
use App\Util\ImageStorage\Normalizer\ImageNormalizer;
use Tests\Unit\TestCase;

class ImageNormalizerTest extends TestCase
{
    /** @var ImageNormalizer */
    private $imageNormalizer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->imageNormalizer = new ImageNormalizer(new ImageTransformerFactory('http://image.com', 'key', 'siteId'));
    }

    public function testNormalize(): void
    {
        $expectedNormalizedData = [
            'name' => 'filepath.jpg',
            'angle' => 0,
            'file_rsf' => '__rsf-0-0',
            'file_id' => 'filepath',
            'file_ext' => 'jpg',
            'preview_url' => 'http://image.com/siteId/filepath__rsf-0-0.jpg?hash=3deb8cd5',
            'h0' => '3deb8cd5',
            'h90' => '206fd0ff',
            'h180' => '161c2b3f',
            'h270' => 'f83876b5',
            'h' => '7309ce2f',
        ];

        $this->assertEquals($expectedNormalizedData, $this->imageNormalizer->normalize(new Image('filepath.jpg'), 0));
    }

    public function testNormalizeWithRotation(): void
    {
        $expectedNormalizedData = [
            'name' => 'filepath.jpg',
            'angle' => 90,
            'file_rsf' => '__rsf-90-90',
            'file_id' => 'filepath',
            'file_ext' => 'jpg',
            'preview_url' => 'http://image.com/siteId/filepath__rsf-90-90__r-90.jpg?hash=3b527b18',
            'h0' => '6687fd8e',
            'h90' => '3b527b18',
            'h180' => 'e6d4fbfa',
            'h270' => '52c34404',
            'h' => 'e3079fad',
        ];

        $this->assertEquals($expectedNormalizedData, $this->imageNormalizer->normalize(new Image('filepath.jpg'), 90, 800, 800, 90));
    }
}
