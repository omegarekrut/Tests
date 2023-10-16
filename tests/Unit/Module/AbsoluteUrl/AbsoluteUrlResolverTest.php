<?php

namespace Tests\Unit\Module\AbsoluteUrl;

use App\Module\AbsoluteUrl\AbsoluteUrlResolver;
use Tests\Unit\TestCase;

class AbsoluteUrlResolverTest extends TestCase
{
    /**
     * @dataProvider getAlreadyAbsoluteUrls
     */
    public function testAlreadyAbsoluteUrlMustNotBeChanged(string $absoluteUrl): void
    {
        $absoluteUrlResolver = new AbsoluteUrlResolver('http://base.url/');

        $actualUrl = $absoluteUrlResolver->resolveUrl($absoluteUrl);

        $this->assertEquals($absoluteUrl, $actualUrl);
    }

    public function getAlreadyAbsoluteUrls(): \Generator
    {
        yield 'absolute url' => [
            'http://already.absolute/url',
        ];

        yield 'relative url' => [
            '//already.absolute/url',
        ];
    }

    /**
     * @dataProvider getPathsAndResolvedUrls
     */
    public function testPathMustBeResolvedAsAbsoluteUrl(string $path, string $baseUrl, string $expectedUrl): void
    {
        $absoluteUrlResolver = new AbsoluteUrlResolver($baseUrl);

        $actualUrl = $absoluteUrlResolver->resolveUrl($path);

        $this->assertEquals($expectedUrl, $actualUrl);
    }

    public function getPathsAndResolvedUrls(): \Generator
    {
        $baseUrl = 'http://base.url/';

        yield 'path' => [
            'path',
            $baseUrl,
            'http://base.url/path',
        ];

        yield 'absolute path' => [
            '/path',
            $baseUrl,
            'http://base.url/path',
        ];
    }
}
