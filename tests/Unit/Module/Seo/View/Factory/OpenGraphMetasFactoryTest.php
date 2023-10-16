<?php

namespace Tests\Unit\Module\Seo\View\Factory;

use App\Module\Seo\TransferObject\SeoPage;
use App\Module\Seo\View\Factory\OpenGraphMetasFactory;
use App\Module\Seo\View\TextHelper\DescriptionPreparerHelper;
use App\Module\Seo\View\TextHelper\TitlePreparerHelper;
use App\Module\Seo\View\ViewObject\Meta;
use Tests\Unit\TestCase;
use Laminas\Diactoros\Uri;

/**
 * @group seo
 * @group seo-view
 */
class OpenGraphMetasFactoryTest extends TestCase
{
    /**
     * @var OpenGraphMetasFactory
     */
    private $openGraphMetasFactory;

    /**
     * @var SeoPage
     */
    private $seoPage;

    protected function setUp(): void
    {
        parent::setUp();

        $this->openGraphMetasFactory = new OpenGraphMetasFactory(
            $this->createTitlePreparerHelper('prepared title'),
            $this->createDescriptionPreparer('prepared description')
        );
        $this->seoPage = new SeoPage();
    }

    public function testCreationLocalMeta(): void
    {
        $metas = $this->openGraphMetasFactory->createMetas($this->seoPage);

        $this->assertContainsMeta('og:locale', 'ru_RU', $metas);
    }

    public function testCreationUrlMeta(): void
    {
        $this->seoPage->setCanonicalLink(new Uri('http://canonical.link/'));
        $metas = $this->openGraphMetasFactory->createMetas($this->seoPage);

        $this->assertContainsMeta('og:url', 'http://canonical.link/', $metas);
    }

    public function testCreationTypeMeta(): void
    {
        $this->seoPage->setCanonicalLink(new Uri('http://canonical.link/'));
        $metas = $this->openGraphMetasFactory->createMetas($this->seoPage);

        $this->assertContainsMeta('og:type', 'website', $metas);

        $this->seoPage->setCanonicalLink(new Uri('http://canonical.link/foo/bar'));
        $metas = $this->openGraphMetasFactory->createMetas($this->seoPage);

        $this->assertContainsMeta('og:type', 'article', $metas);
    }

    public function testCreationTitleMeta(): void
    {
        $metas = $this->openGraphMetasFactory->createMetas($this->seoPage);

        $this->assertContainsMeta('og:title', 'prepared title', $metas);
    }

    public function testCreationDescriptionMeta(): void
    {
        $metas = $this->openGraphMetasFactory->createMetas($this->seoPage);

        $this->assertContainsMeta('og:description', 'prepared description', $metas);
    }

    public function testCreationImageMeta(): void
    {
        $this->seoPage->setImageUrl(new Uri('http://image.uri/'));
        $metas = $this->openGraphMetasFactory->createMetas($this->seoPage);

        $this->assertContainsMeta('og:image', 'http://image.uri/', $metas);
    }

    public function testCreationSiteNameMeta(): void
    {
        $this->seoPage->setSiteName('site name');
        $metas = $this->openGraphMetasFactory->createMetas($this->seoPage);

        $this->assertContainsMeta('og:site_name', 'site name', $metas);
    }

    private function assertContainsMeta(string $property, string $content, array $actualMetas): void
    {
        $actualMetasAsArray = array_map(function (Meta $meta) {
            return [
                'property' => $meta->getProperty(),
                'content' => $meta->getContent(),
                'name' => $meta->getName(),
            ];
        }, $actualMetas);

        $this->assertContains([
            'property' => $property,
            'content' => $content,
            'name' => '',
        ], $actualMetasAsArray);
    }

    private function createTitlePreparerHelper(string $title): TitlePreparerHelper
    {
        $stub = $this->createMock(TitlePreparerHelper::class);
        $stub
            ->method('prepareTitle')
            ->willReturn($title)
        ;

        return $stub;
    }

    private function createDescriptionPreparer(string $description): DescriptionPreparerHelper
    {
        $stub = $this->createMock(DescriptionPreparerHelper::class);
        $stub
            ->method('prepareDescription')
            ->willReturn($description)
        ;

        return $stub;
    }
}
