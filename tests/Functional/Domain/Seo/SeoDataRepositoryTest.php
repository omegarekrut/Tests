<?php

namespace Tests\Functional\Domain\Seo;

use App\Domain\Seo\Entity\SeoData;
use App\Domain\Seo\Repository\SeoDataRepository;
use App\Domain\Seo\Search\SeoSearchData;
use Doctrine\Common\DataFixtures\ReferenceRepository;
use Tests\DataFixtures\ORM\Seo\LoadSeoData;
use Tests\Functional\RepositoryTestCase;
use Laminas\Diactoros\Uri;

/**
 * @group seo
 */
class SeoDataRepositoryTest extends RepositoryTestCase
{
    /** @var ReferenceRepository */
    private $referenceRepository;
    /** @var SeoDataRepository */
    private $repository;
    /** @var SeoData */
    private $expectedSeo;
    /** @var SeoSearchData */
    private $seoSearchData;

    protected function setUp(): void
    {
        parent::setUp();

        $this->referenceRepository = $this->loadFixtures([
            LoadSeoData::class,
        ])->getReferenceRepository();

        $this->expectedSeo = $this->referenceRepository->getReference(LoadSeoData::WITH_QUERY_STRING_AND_OTHER_VALUE);
        $this->repository = $this->getRepository(SeoData::class);
        $this->seoSearchData = new SeoSearchData();
    }

    protected function tearDown(): void
    {
        unset(
            $this->referenceRepository,
            $this->expectedSeo,
            $this->repository,
            $this->seoSearchData
        );

        parent::tearDown();
    }

    public function testFindAllBySearchDataWithEmptySearch(): void
    {
        $expectedCount = count($this->referenceRepository->getIdentities());

        $allSeoDataQueryBuilder = $this->repository->findAllBySearchData($this->seoSearchData);
        $allSeoData = $allSeoDataQueryBuilder->getQuery()->getResult();

        $this->assertCount($expectedCount, $allSeoData);
    }

    public function testFindAllBySearchDataWithUriSearch(): void
    {
        $this->seoSearchData->uri = $this->expectedSeo->getUri();

        $seoDataQueryBuilder = $this->repository->findAllBySearchData($this->seoSearchData);
        $result = $seoDataQueryBuilder->getQuery()->getResult();

        $this->assertContains($this->expectedSeo, $result);
    }

    public function testSearchDataCanBeFoundByTitle(): void
    {
        $this->seoSearchData->title = $this->expectedSeo->getTemplateTitle();

        $seoDataQueryBuilder = $this->repository->findAllBySearchData($this->seoSearchData);
        $result = $seoDataQueryBuilder->getQuery()->getResult();

        $this->assertContains($this->expectedSeo, $result);
    }

    public function testSearchDataCanBeFoundByH1(): void
    {
        $this->seoSearchData->h1 = $this->expectedSeo->getTemplateH1();

        $seoDataQueryBuilder = $this->repository->findAllBySearchData($this->seoSearchData);
        $result = $seoDataQueryBuilder->getQuery()->getResult();

        $this->assertContains($this->expectedSeo, $result);
    }

    public function testSearchDataCanBeFoundByDescription(): void
    {
        $this->seoSearchData->description = $this->expectedSeo->getTemplateDescription();

        $seoDataQueryBuilder = $this->repository->findAllBySearchData($this->seoSearchData);
        $result = $seoDataQueryBuilder->getQuery()->getResult();

        $this->assertContains($this->expectedSeo, $result);
    }

    public function testFindMostSuitableByExistingUri(): void
    {
        $seoData = $this->repository->findMostSuitableByUri(new Uri($this->expectedSeo->getUri()));

        $this->assertEquals($this->expectedSeo, $seoData);
    }

    public function testFindMostSuitableByNotExistingUri(): void
    {
        $seoData = $this->repository->findMostSuitableByUri(new Uri('/nonexist/'));

        $this->assertEmpty($seoData);
    }
}
