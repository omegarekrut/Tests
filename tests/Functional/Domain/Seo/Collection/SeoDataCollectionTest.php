<?php

namespace Tests\Functional\Domain\Seo\Collection;

use App\Domain\Seo\Collection\SeoDataCollection;
use App\Domain\Seo\Entity\SeoData;
use Tests\DataFixtures\ORM\Seo\LoadSeoData;
use Tests\Functional\TestCase;
use Laminas\Diactoros\Uri;

class SeoDataCollectionTest extends TestCase
{
    private $referenceRepository;
    private $seoDataCollection;

    protected function setUp(): void
    {
        parent::setUp();

        $this->referenceRepository = $this->loadFixtures([
            LoadSeoData::class,
        ])->getReferenceRepository();

        $this->seoDataCollection = new SeoDataCollection($this->referenceRepository->getReferences());
    }

    protected function tearDown(): void
    {
        unset(
            $this->referenceRepository,
            $this->seoDataCollection
        );

        parent::tearDown();
    }

    public function testGetMostRelevantSeoData(): void
    {
        $seoDataCollection = new SeoDataCollection([
            new SeoData('/tidings/', '', '', ''),
            new SeoData('/tidings/*', '', '', ''),
            new SeoData('/tidings/?*search*', '', '', ''),
            new SeoData('/tidings/?*search=судака*', '', '', ''),
            new SeoData('/tidings/?*search=судак*', '', '', ''),

        ]);
        $uri = sprintf('/tidings/?%s', http_build_query(['search' => 'судака']));
        $seoData = $seoDataCollection->findOneMostRelevantWithUri(new Uri($uri));

        $this->assertEquals('/tidings/?*search=судака*', $seoData->getUri());
    }

    /**
     * @dataProvider matchedUriList
     */
    public function testMatchedUri(string $referenceName, string $exampleUri): void
    {
        $seoData = $this->referenceRepository->getReference($referenceName);

        $filteredSeoData = $this->seoDataCollection->findOneMostRelevantWithUri(new Uri($exampleUri));

        $this->assertEquals($seoData->getId(), $filteredSeoData->getId());
    }

    public function matchedUriList(): array
    {
        return [
            [LoadSeoData::REGEX_PATTERN, '/articles/'],
            [LoadSeoData::REGEX_PATTERN, '/article/'],
            [LoadSeoData::HUMAN_PATTERN, '/articles/view/123'],
            [LoadSeoData::WITH_QUERY_STRING, '/tackles/?'.http_build_query(['search' => 'xxxx'])],
            [LoadSeoData::WITH_QUERY_STRING_AND_VALUE, '/tackles/?'.http_build_query(['search' => 'судак'])],
        ];
    }

    /**
     * @dataProvider notMatchedUriList
     */
    public function testNotAnyMatched(string $uri): void
    {
        $filteredSeoData = $this->seoDataCollection
            ->findOneMostRelevantWithUri(new Uri($uri));

        $this->assertNull($filteredSeoData);
    }

    public function notMatchedUriList(): array
    {
        return [
            ['___NotAnyMatchingUri'],
            ['/tackles/?abc&b=d'],
        ];
    }
}
