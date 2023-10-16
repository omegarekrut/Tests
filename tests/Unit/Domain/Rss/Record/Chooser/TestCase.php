<?php

namespace Tests\Unit\Domain\Rss\Record\Chooser;

use Tests\Unit\TestCase as UnitTestCase;
use ZenRss\ContentPuller\Collection;
use ZenRss\ContentPuller\ContentType\Image;
use ZenRss\ContentPuller\ContentType\Video;

class TestCase extends UnitTestCase
{
    protected function getContentCollection(array $images = [], array $videos = []): Collection
    {
        $collection = new Collection('');
        $replacement = [];

        foreach ($images as $image) {
            $replacement[] = new Image($image, '', '');
        }

        foreach ($videos as $video) {
            $replacement[] = new Video($video, '', '');
        }

        if (!empty($replacement)) {
            $collection->replaceElement(0, $replacement);
        }

        return $collection;
    }
}
