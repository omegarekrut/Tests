<?php

namespace Tests\Unit\Module\SpamChecker\Collection\Filter;

use App\Module\SpamChecker\Collection\Filter\IntersectionOfCanonicalCommentUrlsFilter;
use App\Module\SpamChecker\SuspectComment;
use Tests\Unit\TestCase;

class IntersectionOfCanonicalCommentUrlsFilterTest extends TestCase
{
    /**
     * @dataProvider getIntersectingLinks
     */
    public function testCommentContainingOneOfUrlShouldBeAccepted(array $expectedUrls, array $commentUrls): void
    {
        $comment = $this->createCommentWithUrls($commentUrls);

        $intersectionOfCanonicalCommentUrlsFilter = new IntersectionOfCanonicalCommentUrlsFilter($expectedUrls);

        $this->assertTrue($intersectionOfCanonicalCommentUrlsFilter($comment));
    }

    public function getIntersectingLinks(): \Generator
    {
        yield [
            [
                'http://foo.bar',
            ],
            [
                'http://foo.bar',
            ],
        ];

        yield [
            [
                'http://foo.bar?test',
            ],
            [
                'http://foo.bar',
            ],
        ];

        yield [
            [
                'https://foo.bar',
            ],
            [
                'http://foo.bar',
            ],
        ];

        yield [
            [
                'http://foo.bar/#fragment',
            ],
            [
                'http://foo.bar/#otherfragment',
            ],
        ];

        yield [
            [
                'http://foo.bar',
            ],
            [
                'http://foo.bar/',
            ]
        ];

        yield [
            [
                'http://foo.bar/path',
            ],
            [
                'http://foo.bar//path//',
            ],
        ];

        yield [
            [
                'http:////foo.bar////',
            ],
            [
                'http://foo.bar',
            ],
        ];

        yield [
            [
                'http://www.foo.bar',
            ],
            [
                'http://foo.bar',
            ],
        ];

        yield [
            [
                'http://m.foo.bar',
            ],
            [
                'http://foo.bar',
            ],
        ];

        yield [
            [
                'http://mobile.foo.bar',
            ],
            [
                'http://foo.bar',
            ],
        ];
    }

    /**
     * @dataProvider getNotIntersectingLinks
     */
    public function testCommentNotContainingOneOfUrlShouldBeRejected(array $expectedUrls, array $commentUrls): void
    {
        $comment = $this->createCommentWithUrls($commentUrls);

        $intersectionOfCanonicalCommentUrlsFilter = new IntersectionOfCanonicalCommentUrlsFilter($expectedUrls);

        $this->assertFalse($intersectionOfCanonicalCommentUrlsFilter($comment));
    }

    public function getNotIntersectingLinks(): \Generator
    {
        yield [
            [
                'http://bar.foo',
            ],
            [
                'http://foo.bar',
            ],
        ];

        yield [
            [
                'http://foo.bar/path',
            ],
            [
                'http://foo.bar/other-path',
            ],
        ];

        yield [
            [
                'http://foo.bar',
            ],
            [
                'http://subdomain.foo.bar',
            ],
        ];
    }

    /**
     * @param string[] $urls
     * @return SuspectComment
     */
    private function createCommentWithUrls(array $urls): SuspectComment
    {
        $stub = $this->createMock(SuspectComment::class);
        $stub
            ->method('getUrlsFromText')
            ->willReturn($urls);

        return $stub;
    }
}
