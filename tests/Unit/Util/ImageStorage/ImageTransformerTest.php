<?php

namespace Tests\Unit\Util\ImageStorage;

use App\Util\ImageStorage\Image;
use App\Util\ImageStorage\ImageTransformer;
use Tests\Unit\TestCase;

class ImageTransformerTest extends TestCase
{
    public function testInit(): void
    {
        $file = $this->createImageTransformer('someName__rs-90__r-42.jpg');

        $this->assertEquals('someName', $file->getFileId());
        $this->assertEquals('jpg', $file->getExtension());
        $this->assertEquals(42, $file->getRotateAngle());
        $this->assertEquals('someName.jpg', $file->getFilename());
    }

    public function testSomeCommands(): void
    {
        $expectedImageLink = 'http://somesite.com/siteId/somename__rs-40-60__cr-1-2-3-4.jpg?hash=2c3d4fec';

        $file = $this->createImageTransformer('somename.jpg');
        $imageLink = (string) $file->withResize(40, 60)->withCrop(1, 2, 3, 4);

        $this->assertEquals($expectedImageLink, $imageLink);
    }

    public function testWithCustomExtension(): void
    {
        $expectedImageLink = 'http://somesite.com/siteId/somename__rs-40-60__cr-1-2-3-4.webp?hash=982cf2d2';

        $file = $this->createImageTransformer('somename.jpg');
        $imageLink = (string) $file->withResize(40, 60)->withCrop(1, 2, 3, 4)->withExtension('webp');

        $this->assertEquals($expectedImageLink, $imageLink);
    }

    public function testWithResize(): void
    {
        $expectedImageLink = 'http://somesite.com/siteId/somename__rs-40-60.jpg?hash=9b604805';

        $file = $this->createImageTransformer('somename.jpg');
        $imageLink = (string) $file->withResize(40, 60);

        $this->assertEquals($expectedImageLink, $imageLink);
    }

    public function testWithResize2Width(): void
    {
        $expectedImageLink = 'http://somesite.com/siteId/somename__rsw-45.jpg?hash=d7ad6b78';

        $file = $this->createImageTransformer('somename.jpg');
        $imageLink = (string) $file->withResize2Width(45);

        $this->assertEquals($expectedImageLink, $imageLink);
    }

    public function testWithResize2Height(): void
    {
        $expectedImageLink = 'http://somesite.com/siteId/somename__rsh-65.jpg?hash=8c939048';

        $file = $this->createImageTransformer('somename.jpg');
        $imageLink = (string) $file->withResize2Height(65);

        $this->assertEquals($expectedImageLink, $imageLink);
    }

    public function testWithResize2Universal(): void
    {
        $expectedImageLinkResize2Universal = 'http://somesite.com/siteId/somename__rsu-40.jpg?hash=a7250524';
        $expectedImageLinkResize2UniversalWithHeight = 'http://somesite.com/siteId/somename__rsu-40-65.jpg?hash=5aa048de';

        $file = $this->createImageTransformer('somename.jpg');

        $imageLinkResize2Universal = (string) $file->withResize2Universal(40);
        $imageLinkResize2UniversalWithHeight = (string) $file->withResize2Universal(40, 65);

        $this->assertEquals($expectedImageLinkResize2Universal, $imageLinkResize2Universal);
        $this->assertEquals($expectedImageLinkResize2UniversalWithHeight, $imageLinkResize2UniversalWithHeight);
    }

    public function testWithResize2Fit(): void
    {
        $expectedImageLink = 'http://somesite.com/siteId/somename__rsf-65-87.jpg?hash=955f8486';

        $file = $this->createImageTransformer('somename.jpg');
        $imageLink = (string) $file->withResize2Fit(65, 87);

        $this->assertEquals($expectedImageLink, $imageLink);
    }

    public function testWithResize2FitHorizontal(): void
    {
        $expectedImageLink = 'http://somesite.com/siteId/somename__rsfh-35-40.jpg?hash=ff9e92af';

        $file = $this->createImageTransformer('somename.jpg');
        $imageLink = (string) $file->withResize2FitHorizontal(35, 40);

        $this->assertEquals($expectedImageLink, $imageLink);
    }

    public function testWithResize2FitFace(): void
    {
        $expectedImageLink = 'http://somesite.com/siteId/somename__rsff-87-65.jpg?hash=2545f47c';

        $file = $this->createImageTransformer('somename.jpg');
        $imageLink = (string) $file->withResize2FitFace(87, 65);

        $this->assertEquals($expectedImageLink, $imageLink);
    }

    public function testWithCrop(): void
    {
        $expectedImageLink = 'http://somesite.com/siteId/somename__cr-5-6-9-7.jpg?hash=1e13549c';

        $file = $this->createImageTransformer('somename.jpg');
        $imageLink = (string) $file->withCrop(5, 6, 9, 7);

        $this->assertEquals($expectedImageLink, $imageLink);
    }

    public function testWithRotate(): void
    {
        $expectedImageLink = 'http://somesite.com/siteId/somename__r-63.jpg?hash=32aa8f0c';

        $file = $this->createImageTransformer('somename.jpg');
        $imageLink = (string) $file->withRotate(63);

        $this->assertEquals($expectedImageLink, $imageLink);
    }

    public function testSearchPatternOfOriginalUrlShouldIgnoreHashAndCommands(): void
    {
        $imageTransformer = $this->createImageTransformer('filename.jpg');
        $resizedImageTransformer = $imageTransformer->withResize2Universal(100, 200);

        $expectedSearchPattern = '/\/\/somesite\.com\/siteId\/filename(.*)\.jpg\?hash=(.*)/i';

        $this->assertEquals($expectedSearchPattern, $imageTransformer->getOriginUrlSearchPattern());
        $this->assertEquals($expectedSearchPattern, $resizedImageTransformer->getOriginUrlSearchPattern());
    }

    private function createImageTransformer(string $someName): ImageTransformer
    {
        return new ImageTransformer(new Image($someName), 'http://somesite.com', 'secretKey', 'siteId');
    }
}
