<?php

namespace Tests\Functional\Domain\Seo\Extension;

use App\Domain\Seo\Extension\SocialMediaImageExtension;
use App\Module\Seo\Extension\SeoContext;
use App\Module\Seo\TransferObject\SeoPage;
use App\Domain\SocialMediaImageMaker\SocialMediaImageUrlGenerator;
use Symfony\Component\Routing\Router;
use Tests\Functional\TestCase;
use Laminas\Diactoros\ServerRequestFactory;
use Laminas\Diactoros\Uri;

/**
 * @group seo
 */
class SocialMediaImageExtensionTest extends TestCase
{
    /** @var SocialMediaImageUrlGenerator */
    private $socialMediaImageUrlGenerator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->socialMediaImageUrlGenerator = $this->getContainer()->get(SocialMediaImageUrlGenerator::class);
    }

    protected function tearDown(): void
    {
        unset($this->socialMediaImageUrlGenerator);

        parent::tearDown();
    }

    /**
     * @dataProvider getUrls
     */
    public function testApply(string $requestUrl, string $expectedImageUrl): void
    {
        $request = ServerRequestFactory::fromGlobals();
        $request = $request->withUri(new Uri($requestUrl));

        $socialMedia = new SocialMediaImageExtension($request, $this->socialMediaImageUrlGenerator);

        $seoPageBuilder = new SeoPage();
        $socialMedia->apply($seoPageBuilder, new SeoContext([]));

        $this->assertEquals($expectedImageUrl, (string) $seoPageBuilder->getImageUrl());
    }

    public function getUrls(): array
    {
        self::bootKernel();

        /** @var Router $urlGenerator */
        $urlGenerator = $this->getContainer()->get('router');

        return [
            'default image url' => [
                '/some/route',
                $urlGenerator->generate('social_media_page_image', ['url' => '/some/route'], $urlGenerator::ABSOLUTE_URL),
            ],
            'record image url' => [
                '/articles/view/10/',
                $urlGenerator->generate('social_media_record_page_image', ['record' => 10], $urlGenerator::ABSOLUTE_URL),
            ],
        ];
    }
}
