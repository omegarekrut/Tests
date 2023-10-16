<?php

namespace Tests\Unit\Twig\AdminHtmlHelper;

use App\Twig\AdminHtmlHelper\AdminRecordPreviewImageUrlGenerator;
use App\Util\ImageStorage\Image;
use App\Util\ImageStorage\ImageTransformerFactory;
use Tests\Unit\TestCase;

class AdminRecordPreviewImageUrlGeneratorTest extends TestCase
{
    private $adminRecordPreviewImageUrlGenerator;

    protected function setUp(): void
    {
        parent::setUp();

        $imageFactory = new ImageTransformerFactory('http://some-url.com', 'key', 'siteId');
        $this->adminRecordPreviewImageUrlGenerator = new AdminRecordPreviewImageUrlGenerator($imageFactory);
    }

    public function testPreviewImagePath(): void
    {
        $image = new Image('some-date-some-hash.jpg');

        $previewImagePath = ($this->adminRecordPreviewImageUrlGenerator)($image);

        $this->assertEquals('http://some-url.com/siteId/some-date-some-hash__rsu-120.jpg?hash=e2b64b08', $previewImagePath);
    }
}
