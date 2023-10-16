<?php

namespace Tests\Faker\Provider\ru_RU;

use Faker\Provider\Base as BaseProvider;

class ImageStorageProvider extends BaseProvider
{
    /**
     * @todo random choose and depends to image storage mock
     */
    public function imageFromStorage(): string
    {
        return 'image-from-image-storage.jpg';
    }
}
