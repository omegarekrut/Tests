<?php

namespace Tests\Unit\Domain\Rss\Record\Chooser;

use App\Domain\Rss\Record\CategoryChooser;
use App\Util\ImageStorage\Collection\ImageCollection;
use App\Util\ImageStorage\Image;

/**
 * @group rss
 */
class CategoryChooserTest extends TestCase
{
    private const IMAGE_CATEGORY = 'Фото';
    private const VIDEO_CATEGORY = 'Видео';

    private $categoryChooser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->categoryChooser = new CategoryChooser(self::IMAGE_CATEGORY, self::VIDEO_CATEGORY);
    }

    protected function tearDown(): void
    {
        unset($this->categoryChooser);

        parent::tearDown();
    }

    public function testChooseWithImage(): void
    {
        $contentCollection = $this->getContentCollection();
        $advancedImages = new ImageCollection([new Image('image2.jpg')]);

        $actualCategories = $this->categoryChooser->choose($contentCollection, $advancedImages);

        $this->assertEquals([self::IMAGE_CATEGORY], $actualCategories);

        $contentCollection = $this->getContentCollection(['image2.jpg'], []);

        $this->assertEquals([self::IMAGE_CATEGORY], $this->categoryChooser->choose($contentCollection, new ImageCollection()));
    }

    public function testChooseWithVideo(): void
    {
        $contentCollection = $this->getContentCollection();
        $advancedVideos = ['video.mp4'];

        $actualCategories = $this->categoryChooser->choose($contentCollection, new ImageCollection(), $advancedVideos);

        $this->assertEquals([self::VIDEO_CATEGORY], $actualCategories);

        $contentCollection = $this->getContentCollection([], ['video.mp4']);

        $this->assertEquals([self::VIDEO_CATEGORY], $this->categoryChooser->choose($contentCollection, new ImageCollection()));
    }

    public function testChooseWithMedia(): void
    {
        $expectedCategories = [self::IMAGE_CATEGORY, self::VIDEO_CATEGORY];

        $contentCollection = $this->getContentCollection();
        $advancedImages = new ImageCollection([new Image('image.jpg')]);
        $advancedVideos = ['video.mp4'];

        $actualCategories = $this->categoryChooser->choose($contentCollection, $advancedImages, $advancedVideos);

        $this->assertEquals($expectedCategories, $actualCategories);
    }

    public function testEmpty(): void
    {
        $contentCollection = $this->getContentCollection();

        $this->assertEmpty($this->categoryChooser->choose($contentCollection, new ImageCollection()));
    }
}
