<?php

namespace Tests\Unit\Domain\Rss\Record\Chooser;

use App\Domain\Rss\Record\EnclosureChooser;
use App\Util\ImageStorage\Collection\ImageCollection;
use App\Util\ImageStorage\Image;
use Tests\Unit\Domain\Rss\Url\AbsoluteImageUrlGeneratorMock;

/**
 * @group rss
 */
class EnclosureChooserTest extends TestCase
{
    public function testChoose(): void
    {
        $advancedImage = new Image('advance-image.jpg');
        $imageInContent = 'image-in-content.jpg';
        $advancedVideo = 'advanced-video.mp4';
        $videoInContent = 'video-in-content.mp4';

        $contentCollection = $this->getContentCollection([$imageInContent], [$videoInContent]);

        $imageUrlGenerator = new AbsoluteImageUrlGeneratorMock();
        $enclosureChooser = new EnclosureChooser($imageUrlGenerator);

        $enclosures = $enclosureChooser->choose($contentCollection, new ImageCollection([$advancedImage, $advancedImage]), [$advancedVideo]);

        $expectedAdvancedImageEnclosureUrl = $imageUrlGenerator->createAbsoluteImageUrl($advancedImage);

        $this->assertCount(4, $enclosures);
        $this->assertEquals($expectedAdvancedImageEnclosureUrl, $enclosures[0]->getUrl());
        $this->assertEquals($imageInContent, $enclosures[1]->getUrl());
        $this->assertEquals($advancedVideo, $enclosures[2]->getUrl());
        $this->assertEquals($videoInContent, $enclosures[3]->getUrl());
    }
}
