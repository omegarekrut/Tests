<?php

namespace Tests\Unit\Domain\Rss\Url;

use App\Domain\Rss\Url\AbsoluteImageUrlGeneratorInterface;
use App\Util\ImageStorage\Image;

class AbsoluteImageUrlGeneratorMock implements AbsoluteImageUrlGeneratorInterface
{
    public function createAbsoluteImageUrl(Image $image): string
    {
        return sprintf('http://image.com/%s', $image->getFilename());
    }
}
