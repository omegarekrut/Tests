<?php

namespace Tests\Unit\Module\Seo\View\Factory;

use App\Module\Seo\TransferObject\PagePagination;
use App\Module\Seo\TransferObject\SeoPage;
use App\Module\Seo\View\Factory\PageNavigationLinksFactory;
use App\Module\Seo\View\ViewObject\Link;
use Tests\Unit\TestCase;

/**
 * @group seo
 * @group seo-view
 */
class PageNavigationLinksFactoryTest extends TestCase
{
    /**
     * @dataProvider getCases
     */
    public function testGetHtmlLinks(
        int $pageNumber,
        int $totalPageCount,
        ?string $previousPageUrl = null,
        ?string $nextPageUrl = null,
        array $expectedLinks
    ): void {
        $seoPage = new SeoPage();
        $seoPage->setPagination(new PagePagination($pageNumber, $totalPageCount, $previousPageUrl, $nextPageUrl));
        $pageNavigationLinks = new PageNavigationLinksFactory();

        $this->assertEqualsMetas($expectedLinks, $pageNavigationLinks->createLinks($seoPage));
    }

    private function assertEqualsMetas(array $expectedLinks, array $actualLinks): void
    {
        $this->assertEquals(self::linksAsArray($expectedLinks), self::linksAsArray($actualLinks));
    }

    private static function linksAsArray(array $links): array
    {
        return array_map(function (Link $link) {
            return [
                'rel' => $link->getRel(),
                'href' => $link->getHref(),
            ];
        }, $links);
    }

    public function getCases(): array
    {
        return [
            'both links' => [
                2,
                3,
                '/?page=1',
                '/?page=3',
                [
                    new Link('prev', '/?page=1'),
                    new Link('next', '/?page=3'),
                ],
            ],
            'first link' => [
                1,
                2,
                null,
                '/?page=2',
                [
                    new Link('next', '/?page=2'),
                ],
            ],
            'last link' => [
                2,
                2,
                '/?page=1',
                null,
                [
                    new Link('prev', '/?page=1'),
                ],
            ],
            'without links' => [
                1,
                1,
                null,
                null,
                [],
            ],
        ];
    }
}
