<?php

namespace Tests\Unit\Domain\Seo\Extension;

use App\Domain\Seo\Extension\MicroDataExtension;
use App\Module\Seo\Extension\SeoContext;
use App\Module\Seo\JsonLd\AggregateRating;
use App\Module\Seo\JsonLd\Publisher;
use App\Module\Seo\TransferObject\MicroFormatData;
use App\Module\Seo\TransferObject\SeoPage;
use Carbon\Carbon;
use Tests\Unit\TestCase;
use Laminas\Diactoros\Uri;

class MicroDataExtensionTest extends TestCase
{
    /** @var MicroDataExtension */
    private $microDataExtension;

    protected function setUp(): void
    {
        parent::setUp();

        $this->microDataExtension = new MicroDataExtension(
            new Publisher('test', 'http://www.test.ru/icon.png')
        );
    }

    public function testNotSetMicroData(): void
    {
        $seoPage = new SeoPage();

        $this->microDataExtension->apply($seoPage, new SeoContext([]));

        $this->assertEmpty($seoPage->getMicroData());
    }

    /**
     * @param mixed[] $expectedData
     *
     * @dataProvider setMicroDataDataProvider
     */
    public function testSetMicroData(SeoPage $seoPage, MicroFormatData $microFormatData, array $expectedData): void
    {
        $this->microDataExtension->apply($seoPage, new SeoContext([$microFormatData]));

        $this->assertEquals($expectedData, $seoPage->getMicroData()->getDataForJson());
    }

    public function setMicroDataDataProvider(): \Generator
    {
        yield self::createDataWithCanonicalLink();

        yield self::createDataWithRewritingHeader();

        yield self::createDataWithRewritingTitle();

        yield self::createDataWithPublishedAndModifiedDates();

        yield self::createDataWithImageUrl();

        yield self::createDataWithRating();
    }

    /**
     * @return mixed[]
     */
    private static function createDataWithPublishedAndModifiedDates(): array
    {
        $expectedDatePublish = Carbon::now();
        $microFormatWithPublishedDate = new MicroFormatData('', '', '', '');
        $microFormatWithPublishedDate->setDatePublish($expectedDatePublish);
        $microFormatWithPublishedDate->setDateModified($expectedDatePublish);

        return [
            new SeoPage(),
            $microFormatWithPublishedDate,
            [
                '@context' => 'http://schema.org',
                '@type' => '',
                'author' => '',
                'description' => '',
                'headline' => '',
                'name' => '',
                'publisher' => [
                    '@type' => 'Organization',
                    'name' => 'test',
                    'logo' => [
                        '@type' => 'ImageObject',
                        'url' => 'http://www.test.ru/icon.png',
                    ],
                ],
                'datePublished' => $expectedDatePublish->format('Y-m-d\TH:i:sO'),
                'dateModified' => $expectedDatePublish->format('Y-m-d\TH:i:sO'),
            ],
        ];
    }

    /**
     * @return mixed[]
     */
    private static function createDataWithRewritingTitle(): array
    {
        return [
            (new SeoPage())->setTitle('rewrite Title'),
            new MicroFormatData('testType', 'name article', 'short description', 'author name'),
            [
                '@context' => 'http://schema.org',
                '@type' => 'testType',
                'author' => 'author name',
                'description' => 'short description',
                'headline' => 'rewrite Title',
                'name' => 'rewrite Title',
                'publisher' => [
                    '@type' => 'Organization',
                    'name' => 'test',
                    'logo' => [
                        '@type' => 'ImageObject',
                        'url' => 'http://www.test.ru/icon.png',
                    ],
                ],
            ],
        ];
    }

    /**
     * @return mixed[]
     */
    private static function createDataWithRewritingHeader(): array
    {
        return [
            (new SeoPage())->setH1('rewrite H1'),
            new MicroFormatData('testType', 'name article', 'short description', 'author name'),
            [
                '@context' => 'http://schema.org',
                '@type' => 'testType',
                'author' => 'author name',
                'description' => 'short description',
                'headline' => 'rewrite H1',
                'name' => 'rewrite H1',
                'publisher' => [
                    '@type' => 'Organization',
                    'name' => 'test',
                    'logo' => [
                        '@type' => 'ImageObject',
                        'url' => 'http://www.test.ru/icon.png',
                    ],
                ],
            ],
        ];
    }

    /**
     * @return mixed[]
     */
    private static function createDataWithCanonicalLink(): array
    {
        return [
            (new SeoPage())->setCanonicalLink(new Uri('/test')),
            new MicroFormatData('testType', 'name article', 'short description', 'author name'),
            [
                '@context' => 'http://schema.org',
                '@type' => 'testType',
                'author' => 'author name',
                'description' => 'short description',
                'headline' => '',
                'name' => '',
                'publisher' => [
                    '@type' => 'Organization',
                    'name' => 'test',
                    'logo' => [
                        '@type' => 'ImageObject',
                        'url' => 'http://www.test.ru/icon.png',
                    ],
                ],
                'mainEntityOfPage' => '/test',
                'url' => '/test',
            ],
        ];
    }

    /**
     * @return mixed[]
     */
    private static function createDataWithImageUrl(): array
    {
        return [
            (new SeoPage())->setImageUrl(new Uri('http://foo.bar')),
            new MicroFormatData('', '', '', ''),
            [
                '@context' => 'http://schema.org',
                '@type' => '',
                'author' => '',
                'description' => '',
                'headline' => '',
                'name' => '',
                'publisher' => [
                    '@type' => 'Organization',
                    'name' => 'test',
                    'logo' => [
                        '@type' => 'ImageObject',
                        'url' => 'http://www.test.ru/icon.png',
                    ],
                ],
                'image' => [
                    '@type' => 'ImageObject',
                    'url' => 'http://foo.bar',
                ],
            ],
        ];
    }

    /**
     * @return mixed[]
     */
    private static function createDataWithRating(): array
    {
        $microFormatWithAggregateRating = new MicroFormatData('', '', '', '');
        $microFormatWithAggregateRating->setAggregateRating(new AggregateRating('4.3', 12));

        return [
            new SeoPage(),
            $microFormatWithAggregateRating,
            [
                '@context' => 'http://schema.org',
                '@type' => '',
                'author' => '',
                'description' => '',
                'headline' => '',
                'name' => '',
                'publisher' => [
                    '@type' => 'Organization',
                    'name' => 'test',
                    'logo' => [
                        '@type' => 'ImageObject',
                        'url' => 'http://www.test.ru/icon.png',
                    ],
                ],
                'aggregateRating' => [
                    '@type' => 'AggregateRating',
                    'ratingValue' => '4.3',
                    'ratingCount' => '12',
                ],
            ],
        ];
    }
}
