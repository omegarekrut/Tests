<?php

namespace Tests\Unit\Module\ArticleContents;

use App\Module\CanonicalUrl\CanonicalUrl;
use Tests\Unit\TestCase;

/**
 * @group canonical-url
 */
class CanonicalUrlTest extends TestCase
{
    /**
     * @dataProvider getCanonicalUrls
     */
    public function testCanonicalUrlCanBeCreatedByFullUrl(string $fullUrl, string $expectedUrl, string $expectedDomain): void
    {
        $canonicalUrl = CanonicalUrl::createByUrl($fullUrl);

        $this->assertEquals($expectedUrl, (string) $canonicalUrl);
        $this->assertEquals($expectedDomain, $canonicalUrl->getDomain());
    }

    public function getCanonicalUrls(): \Generator
    {
        yield [
            'http://foo.bar',
            '//foo.bar',
            'foo.bar',
        ];

        yield [
            'http://foo.bar?test',
            '//foo.bar',
            'foo.bar',
        ];

        yield [
            'https://foo.bar',
            '//foo.bar',
            'foo.bar',
        ];

        yield [
            'http://foo.bar/#fragment',
            '//foo.bar',
            'foo.bar',
        ];

        yield [
            'http://foo.bar',
            '//foo.bar',
            'foo.bar',
        ];

        yield [
            'http://foo.bar/path',
            '//foo.bar/path',
            'foo.bar',
        ];

        yield [
            'http:////foo.bar////',
            '//foo.bar',
            'foo.bar',
        ];

        yield [
            'http://www.foo.bar',
            '//foo.bar',
            'foo.bar',
        ];

        yield [
            'http://m.foo.bar',
            '//foo.bar',
            'foo.bar',
        ];

        yield [
            'http://mobile.foo.bar',
            '//foo.bar',
            'foo.bar',
        ];

        yield [
            'https://mobile.foo.bar',
            '//foo.bar',
            'foo.bar',
        ];
    }
}
