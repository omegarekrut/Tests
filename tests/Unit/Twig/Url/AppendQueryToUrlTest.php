<?php

namespace Tests\Unit\Twig\Url;

use App\Twig\Url\AppendQueryToUrl;
use Tests\Unit\TestCase;

/**
 * @group weekly-letter
 */
class AppendQueryToUrlTest extends TestCase
{
    /**
     * @dataProvider urlAndQueryProvider
     */
    public function testAppendQueryToUrl(string $url, string $queryToAppend, string $expectedUrl): void
    {
        $appendQueryToUrl = new AppendQueryToUrl();

        $urlWithQuery = $appendQueryToUrl($url, $queryToAppend);

        $this->assertEquals($expectedUrl, $urlWithQuery);
    }

    /**
     * @return mixed[]
     */
    public function urlAndQueryProvider(): array
    {
        return [
            [
                'http://example.com/some-path/123',
                '',
                'http://example.com/some-path/123',
            ],
            [
                '/some-path/123',
                '',
                '/some-path/123',
            ],
            [
                'http://example.com?page=3',
                '',
                'http://example.com?page=3',
            ],
            [
                '/some-path/123?query=text',
                '',
                '/some-path/123?query=text',
            ],
            [
                'http://example.com/some-path/123',
                'utm_source=text',
                'http://example.com/some-path/123?utm_source=text',
            ],
            [
                '/some-path/123',
                'utm_source=text',
                '/some-path/123?utm_source=text',
            ],
            [
                'http://example.com?page=3',
                'utm_source=text',
                'http://example.com?page=3&utm_source=text',
            ],
            [
                '/some-path/123?query=text',
                'utm_source=text',
                '/some-path/123?query=text&utm_source=text',
            ],
        ];
    }
}
