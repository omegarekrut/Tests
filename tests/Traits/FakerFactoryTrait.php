<?php

namespace Tests\Traits;

use Faker;
use Tests\Faker\FakerFactory;

trait FakerFactoryTrait
{
    /**
     * @return Faker\Generator
     */
    protected function getFaker(): Faker\Generator
    {
        return FakerFactory::create();
    }
}
