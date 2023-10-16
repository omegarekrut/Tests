<?php

namespace Tests\Unit\Module\YoutubeVideo;

use App\Module\YoutubeVideo\VideoUrlChecker;
use Tests\Unit\TestCase;

class VideoUrlCheckerTest extends TestCase
{
    /**
     * @dataProvider getCases
     */
    public function testCheckYoutubeUrl(string $url, bool $expectedIsYoutube): void
    {
        $this->assertEquals($expectedIsYoutube, VideoUrlChecker::isYoutubeUrl($url));
    }

    public function getCases(): array
    {
        return [
            'good youtube url' => [
                'http://youtu.be/zN8oiapo8ZU',
                true,
            ],
            'good youtube url another' => [
                'https://www.youtube/zN8oiapo8ZU',
                true,
            ],
            'good youtube url with spaces on the sides' => [
                '  http://www.youtube/zN8oiapo8ZU  ',
                true,
            ],
            'wrong youtube url with front text' => [
                "u041fu043eu0441u043bu0435u0434u0441u0442u0432u0438u0435 u043eu0442
                u0431u0440u0430u043au043eu043du044cu0435u0440u043eu0432 u0432u044bu043bu043eu0432u043bu0435u043du043e u043eu0442
                u0446u0435u043fu043eu043c u043cu043eu0440u043cu044bu0448u043au0438
                http://youtu.be/zN8oiapo8ZU",
                false,
            ],
            'wrong youtube url with back text' => [
                "http://youtu.be/zN8oiapo8ZU u041fu043eu0441u043bu0435u0434u0441u0442u0432u0438u0435 u043eu0442",
                false,
            ],
            'wrong youtube url with russian text' => [
                " тест http://youtu.be/zN8oiapo8ZU тест",
                false,
            ],
            'wrong youtube url with prefix' => [
                "asdasdhttp://youtu.be/zN8oiapo8ZU",
                false,
            ],
        ];
    }
}
