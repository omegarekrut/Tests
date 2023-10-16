<?php

namespace Tests\Unit\Domain\Seo\Extension\Routing;

use App\Domain\Hashtag\Entity\Hashtag;
use App\Domain\Seo\Entity\SeoData;
use App\Domain\Seo\Extension\Routing\HashtagRouteExtension;
use App\Domain\Seo\Repository\SeoDataRepository;
use App\Module\Seo\Extension\Routing\Route;
use App\Module\Seo\Extension\SeoContext;
use App\Module\Seo\TransferObject\SeoPage;
use Tests\Unit\TestCase;
use Laminas\Diactoros\Uri;

/**
 * @group seo
 */
class HashtagRouteExtensionTest extends TestCase
{
    public function testSeoPageMustContainsInformationAboutHashtagOnHashtagPages(): void
    {
        $seoPage = $this->createSeoPageWithCanonicalLink();

        $hashtagRouteExtension = new HashtagRouteExtension($this->createMock(SeoDataRepository::class));
        $hashtagRouteExtension->apply($seoPage, $this->createHashtagViewContext());

        $this->assertEquals('hashtag', $seoPage->getTitle());
        $this->assertEquals('hashtag', $seoPage->getH1());
        $this->assertFalse($seoPage->isIndexingByRobotsEnabled());
    }

    public function testIndexByRobotsMustBeEnabledForPagesWithOverwrittenSeoData(): void
    {
        $seoPage = $this->createSeoPageWithCanonicalLink();

        $seoDataRepository = $this->createConfiguredMock(SeoDataRepository::class, [
            'findMostSuitableByUri' => $this->createMock(SeoData::class),
        ]);

        $hashtagRouteExtension = new HashtagRouteExtension($seoDataRepository);
        $hashtagRouteExtension->apply($seoPage, $this->createHashtagViewContext());

        $this->assertTrue($seoPage->isIndexingByRobotsEnabled());
    }

    public function testSeoCannotBeAppliedForPageWithoutCanonicalLink(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Hashtag seo page must contains canonical link');

        $hashtagRouteExtension = new HashtagRouteExtension($this->createMock(SeoDataRepository::class));
        $hashtagRouteExtension->apply(new SeoPage(), $this->createHashtagViewContext());
    }

    /**
     * @dataProvider getRoutesForCheckSupports
     */
    public function testIsSupportedRoutes(string $routeName, bool $expectedIsSupported): void
    {
        $route = $this->createConfiguredMock(Route::class, [
            'getName' => $routeName,
        ]);

        $hashtagRouteExtension = new HashtagRouteExtension($this->createMock(SeoDataRepository::class));

        /** @var Route $route */
        $this->assertEquals($expectedIsSupported, $hashtagRouteExtension->isSupported($route));
    }

    /**
     * @return mixed[][]
     */
    public function getRoutesForCheckSupports(): array
    {
        return [
            'hashtag_view_route' => ['hashtag_view', true],
            'hashtag_view_pagination_route' => ['hashtag_view_pagination', true],
        ];
    }

    private function createSeoPageWithCanonicalLink(): SeoPage
    {
        $seoPage = new SeoPage();
        $seoPage->setCanonicalLink(new Uri('http://canonical.link/path'));

        return $seoPage;
    }

    private function createHashtagViewContext(): SeoContext
    {
        $route = new Route('hashtag_view', new Uri(''));
        $hashtag = new Hashtag('hashtag');

        return new SeoContext([$route, $hashtag]);
    }
}
