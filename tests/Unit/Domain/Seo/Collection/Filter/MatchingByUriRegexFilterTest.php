<?php

namespace Tests\Unit\Domain\Seo\Collection\Filter;

use App\Domain\Seo\Collection\Filter\MatchingByUriRegexFilter;
use App\Domain\Seo\Entity\SeoData;
use Tests\Unit\TestCase;
use Laminas\Diactoros\Uri;

class MatchingByUriRegexFilterTest extends TestCase
{
    /**
     * @dataProvider matchedUriList
     */
    public function testShouldReturnTrueIfUriMatch(SeoData $seoData, string $uri): void
    {
        $matchingByUriRegexFilter = new MatchingByUriRegexFilter(new Uri($uri));

        $this->assertTrue(($matchingByUriRegexFilter)($seoData));
    }

    public function matchedUriList(): array
    {
        return [
            [
                new SeoData('/tidings/', '', '', ''),
                '/tidings/',
            ],
            [
                new SeoData('/tidings/*', '', '', ''),
                '/tidings/',
            ],
            [
                new SeoData('/tidings/?*search*', '', '', ''),
                sprintf('/tidings/?%s', http_build_query(['search' => ''])),
            ],
            [
                new SeoData('/tidings/?*search=судака*', '', '', ''),
                sprintf('/tidings/?%s', http_build_query(['search' => 'судака'])),
            ],
        ];
    }

    /**
     * @dataProvider notMatchedUriList
     */
    public function testShouldReturnFalseIfUriNotMatch(SeoData $seoData, string $uri): void
    {
        $matchingByUriRegexFilter = new MatchingByUriRegexFilter(new Uri($uri));

        $this->assertFalse(($matchingByUriRegexFilter)($seoData));
    }

    public function notMatchedUriList(): array
    {
        return [
            [
                new SeoData('/tackles/', '', '', ''),
                '/articles/',
            ],
            [
                new SeoData('/tackles/', '', '', ''),
                '___NotAnyMatchingUri',
            ],
            [
                new SeoData('/tackles/', '', '', ''),
                '/tackles/?abc&b=d',
            ],
        ];
    }
}
