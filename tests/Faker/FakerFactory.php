<?php

namespace Tests\Faker;

use Faker\Factory;
use Faker\Generator;
use Tests\Faker\Provider\ru_RU\ArticleMarkupProvider;
use Tests\Faker\Provider\ru_RU\ImageStorageProvider;
use Tests\Faker\Provider\ru_RU\InternetProvider;
use Tests\Faker\Provider\ru_RU\MarkupProvider;
use Tests\Faker\Provider\ru_RU\TextProvider;
use Tests\Faker\Provider\ru_RU\VideoProvider;

class FakerFactory extends Factory
{
    public const DEFAULT_LOCALE = 'ru_RU';

    private static $customProviders = [
        InternetProvider::class,
        TextProvider::class,
        MarkupProvider::class,
        VideoProvider::class,
        ImageStorageProvider::class,
        ArticleMarkupProvider::class,
    ];

    public static function create($locale = self::DEFAULT_LOCALE): Generator
    {
        $generator = parent::create($locale);

        foreach (self::$customProviders as $providerClass) {
            $generator->addProvider(new $providerClass($generator));
        }

        return $generator;
    }
}
