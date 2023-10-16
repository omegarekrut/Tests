<?php

namespace Tests\Unit\Twig\Asset;

use App\Twig\Asset\AbsoluteUrlInContext;
use Tests\Unit\TestCase;

/**
 * @group twig
 */
class AbsoluteUrlInContextTest extends TestCase
{
    /**
     * @dataProvider getTextWithUrls
     */
    public function testFilter(string $text, string $expectedTextWithAbsoluteUrls): void
    {
        $filter = new AbsoluteUrlInContext('http://site.ru');

        $this->assertEquals($expectedTextWithAbsoluteUrls, $filter($text));
    }

    public function getTextWithUrls(): \Generator
    {
        yield [
            '<a href="http://www.site.ru/users/">Text</a> <a href="/video/">',
            '<a href="http://www.site.ru/users/">Text</a> <a href="http://site.ru/video/">',
        ];

        yield [
            '<a href="/video/" title="заголовок 2">заголовок 2</a> Сообщество, посвященное творческой деятельности',
            '<a href="http://site.ru/video/" title="заголовок 2">заголовок 2</a> Сообщество, посвященное творческой деятельности',
        ];

        yield [
            '<a href="http://www.site.ru/users/">заголовок</a>',
            '<a href="http://www.site.ru/users/">заголовок</a>',
        ];
    }
}
