<?php

namespace Tests\Faker\Provider\ru_RU;

use Faker\Provider\Base as BaseProvider;

class VideoProvider extends BaseProvider
{
    public function videoCode(int $width = 640, int $height = 480): string
    {
        $url = $this->videoUrl();

        return sprintf('<iframe width="%s" height="%s" src="%s"></iframe>', $width, $height, $url);
    }

    /**
     * @todo random choose
     */
    public function videoUrl(): string
    {
        $id = self::$youtubeVideoIds[array_rand(self::$youtubeVideoIds, 1)];

        return sprintf('//www.youtube.com/embed/%s?rel=0&amp;enablejsapi=1', $id);
    }

    private static $youtubeVideoIds = [
        'XwklJ3J6E8Q',
        '8Mm9tVHC7gM',
        'GeK2T3xgF08',
        'jIgwwAMYmFA',
        's2nLryU_qME',
        'xcnmxQ_Mhx4',
        'nJf3AMtJnWo',
        'fAWjOjHAmMk',
        'PqYpt8JjcLc',
        'WWhJ6zKIUmc',
        'RgsyKzQIHMg',
        'yQfl0dgYUrU',
        'UMFDfN4VVUk',
        'jcg4obSCUAI',
        'NHg2U6PYLBY',
        'O628NUjfzhs',
        'fYC3KY7Lvl4',
        'fabXO9Hvtpg',
        '5BoP5A110Qw',
        'e06oxOFCGVs',
        'fVJPLPJmoeY',
        'rX19KvosyL8',
        'aUO6mzv5Ivo',
        'qrBGpJNzWHk',
        'b8jvO5iqtSg',
        'vn6jkqY8r0A',
        'WBQAK0sN6HE',
        'HnrrrPp7V2Y',
        'x5vYc7tFKWQ',
        '8xVdKo4wLh8',
        'RU8MKMimJXo',
    ];
}
