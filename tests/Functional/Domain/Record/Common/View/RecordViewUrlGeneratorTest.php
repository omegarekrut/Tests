<?php

namespace Tests\Functional\Domain\Record\Common\View;

use App\Domain\Record\Article\Entity\Article;
use App\Domain\Record\Common\Entity\Record;
use App\Domain\Record\Common\View\RecordViewUrlGenerator;
use App\Domain\Record\CompanyArticle\Entity\CompanyArticle;
use App\Domain\Record\CompanyReview\Entity\CompanyReview;
use App\Domain\Record\Gallery\Entity\Gallery;
use App\Domain\Record\Map\Entity\Map;
use App\Domain\Record\News\Entity\News;
use App\Domain\Record\Tackle\Entity\Tackle;
use App\Domain\Record\Tackle\Entity\TackleReview;
use App\Domain\Record\Tidings\Entity\Tidings;
use App\Domain\Record\Video\Entity\Video;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Tests\DataFixtures\ORM\Record\CompanyArticle\LoadPaidReservoirsCompanyArticle;
use Tests\DataFixtures\ORM\Record\CompanyReview\LoadCompanyReviews;
use Tests\DataFixtures\ORM\Record\LoadArticles;
use Tests\DataFixtures\ORM\Record\LoadGallery;
use Tests\DataFixtures\ORM\Record\LoadMaps;
use Tests\DataFixtures\ORM\Record\LoadNews;
use Tests\DataFixtures\ORM\Record\LoadTackleReviews;
use Tests\DataFixtures\ORM\Record\LoadTackles;
use Tests\DataFixtures\ORM\Record\LoadVideos;
use Tests\DataFixtures\ORM\Record\Tidings\LoadTidingsWithHashtag;
use Tests\Functional\TestCase as FunctionalTestCase;

class RecordViewUrlGeneratorTest extends FunctionalTestCase
{
    private Article $article;
    private Gallery $gallery;
    private Map $map;
    private News $news;
    private Video $video;
    private Tidings $tidings;
    private Tackle $tackle;
    private TackleReview $tackleReview;
    private CompanyArticle $companyArticle;
    private CompanyReview $companyReview;
    private RecordViewUrlGenerator $recordViewUrlGenerator;

    protected function setUp(): void
    {
        parent::setUp();

        $referenceRepository = $this->loadFixtures([
            LoadArticles::class,
            LoadGallery::class,
            LoadMaps::class,
            LoadNews::class,
            LoadVideos::class,
            LoadTidingsWithHashtag::class,
            LoadTackles::class,
            LoadTackleReviews::class,
            LoadPaidReservoirsCompanyArticle::class,
            LoadCompanyReviews::class,
        ])->getReferenceRepository();

        $this->article = $referenceRepository->getReference(LoadArticles::getRandReferenceName());
        $this->gallery = $referenceRepository->getReference(LoadGallery::getRandReferenceName());
        $this->map = $referenceRepository->getReference(LoadMaps::getRandReferenceName());
        $this->news = $referenceRepository->getReference(LoadNews::getRandReferenceName());
        $this->video = $referenceRepository->getReference(LoadVideos::getRandReferenceName());
        $this->tidings = $referenceRepository->getReference(LoadTidingsWithHashtag::getRandReferenceName());
        $this->tackle = $referenceRepository->getReference(LoadTackles::getRandReferenceName());
        $this->tackleReview = $referenceRepository->getReference(LoadTackleReviews::getRandReferenceName());
        $this->companyArticle = $referenceRepository->getReference(LoadPaidReservoirsCompanyArticle::REFERENCE_NAME);
        $this->companyReview = $referenceRepository->getReference(LoadCompanyReviews::REFERENCE_NAME);

        $this->recordViewUrlGenerator = $this->getContainer()->get(RecordViewUrlGenerator::class);
    }

    protected function tearDown(): void
    {
        unset(
            $this->article,
            $this->gallery,
            $this->map,
            $this->news,
            $this->video,
            $this->tidings,
            $this->tackle,
            $this->tackleReview,
            $this->companyArticle,
            $this->companyReview,
            $this->recordViewUrlGenerator
        );

        parent::tearDown();
    }

    public function testGenerateUrlForArticles(): void
    {
        $expectedUrl = sprintf('/articles/view/%d/', $this->article->getId());
        $actualUrl = $this->recordViewUrlGenerator->generate($this->article);

        $this->assertEquals($expectedUrl, $actualUrl);
    }

    public function testGenerateUrlForGallery(): void
    {
        $expectedUrl = sprintf('/gallery/view/%d/', $this->gallery->getId());
        $actualUrl = $this->recordViewUrlGenerator->generate($this->gallery);

        $this->assertEquals($expectedUrl, $actualUrl);
    }

    public function testGenerateUrlForMaps(): void
    {
        $expectedUrl = sprintf('/maps/view/%d/', $this->map->getId());
        $actualUrl = $this->recordViewUrlGenerator->generate($this->map);

        $this->assertEquals($expectedUrl, $actualUrl);
    }

    public function testGenerateUrlForNews(): void
    {
        $expectedUrl = sprintf('/news/view/%d/', $this->news->getId());
        $actualUrl = $this->recordViewUrlGenerator->generate($this->news);

        $this->assertEquals($expectedUrl, $actualUrl);
    }

    public function testGenerateUrlForVideo(): void
    {
        $expectedUrl = sprintf('/video/view/%d/', $this->video->getId());
        $actualUrl = $this->recordViewUrlGenerator->generate($this->video);

        $this->assertEquals($expectedUrl, $actualUrl);
    }

    public function testGenerateUrlForTidings(): void
    {
        $expectedUrl = sprintf('/tidings/view/%d/', $this->tidings->getId());
        $actualUrl = $this->recordViewUrlGenerator->generate($this->tidings);

        $this->assertEquals($expectedUrl, $actualUrl);
    }

    public function testGenerateUrlForTackles(): void
    {
        $expectedUrl = sprintf('/tackles/view/%d/', $this->tackle->getId());
        $actualUrl = $this->recordViewUrlGenerator->generate($this->tackle);

        $this->assertEquals($expectedUrl, $actualUrl);
    }

    public function testGenerateUrlForCompanyArticles(): void
    {
        $expectedUrl = sprintf('/company-articles/view/%d/', $this->companyArticle->getId());
        $actualUrl = $this->recordViewUrlGenerator->generate($this->companyArticle);

        $this->assertEquals($expectedUrl, $actualUrl);
    }

    public function testGenerateUrlForCompanyReviews(): void
    {
        $expectedUrl = sprintf('/company-reviews/view/%d/', $this->companyReview->getId());
        $actualUrl = $this->recordViewUrlGenerator->generate($this->companyReview);

        $this->assertEquals($expectedUrl, $actualUrl);
    }

    public function testGenerateUrlForTackleReviews(): void
    {
        $expectedUrl = sprintf('/tackles/review/%d/', $this->tackleReview->getId());
        $actualUrl = $this->recordViewUrlGenerator->generate($this->tackleReview);

        $this->assertEquals($expectedUrl, $actualUrl);
    }

    public function testUnknownRecordTypeMustCauseExceptionOnGeneration(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Record');
        $this->expectExceptionMessage('doesn\'t have view url.');

        $this->recordViewUrlGenerator->generate($this->createMock(Record::class));
    }

    public function testRecordViewUrlCanBeGeneratedAsAbsoluteUrl(): void
    {
        $expectedPath = sprintf('/articles/view/%d/', $this->article->getId());

        $actualUrl = $this->recordViewUrlGenerator->generate($this->article, UrlGeneratorInterface::ABSOLUTE_URL);

        $this->assertStringContainsString($expectedPath, $actualUrl);
        $this->assertStringContainsString('http', $actualUrl);
    }
}
