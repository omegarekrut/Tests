<?php

namespace Tests\DataFixtures\Helper;

use App\Util\ImageStorage\Image;
use Faker\Generator;

class MediaHelper
{
    private $generator;

    public function __construct(Generator $generator)
    {
        $this->generator = $generator;
    }

    public function createImage(): Image
    {
        return new Image($this->generator->imageFromStorage());
    }

    /**
     * @todo replace to video entity
     */
    public function createVideo(): string
    {
        return $this->generator->videoCode();
    }
}
