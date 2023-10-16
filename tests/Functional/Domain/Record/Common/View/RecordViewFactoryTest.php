<?php

namespace Tests\Functional\Domain\Record\Common\View;

use App\Domain\Record\Article\Entity\Article;
use App\Domain\Record\Article\View\ArticleView;
use App\Domain\Record\Common\Entity\Record;
use App\Domain\Record\Common\View\RecordViewFactory;
use App\Domain\Record\CompanyArticle\Entity\CompanyArticle;
use App\Domain\Record\CompanyArticle\View\CompanyArticleView;
use App\Domain\Record\Gallery\Entity\Gallery;
use App\Domain\Record\Gallery\View\GalleryView;
use App\Domain\Record\Map\Entity\Map;
use App\Domain\Record\Map\View\MapView;
use App\Domain\Record\News\Entity\News;
use App\Domain\Record\News\View\NewsView;
use App\Domain\Record\Tackle\Entity\Tackle;
use App\Domain\Record\Tackle\Entity\TackleReview;
use App\Domain\Record\Tackle\View\TackleReviewView;
use App\Domain\Record\Tackle\View\TackleView;
use App\Domain\Record\Tidings\View\TidingsView;
use App\Domain\Record\Video\Entity\Video;
use App\Domain\Record\Video\View\VideoView;
use InvalidArgumentException;
use Tests\DataFixtures\ORM\Record\CompanyArticle\LoadPaidReservoirsCompanyArticle;
use Tests\DataFixtures\ORM\Record\LoadArticles;
use Tests\DataFixtures\ORM\Record\LoadGallery;
use Tests\DataFixtures\ORM\Record\LoadMaps;
use Tests\DataFixtures\ORM\Record\LoadNews;
use Tests\DataFixtures\ORM\Record\LoadTackleReviews;
use Tests\DataFixtures\ORM\Record\LoadTackles;
use Tests\DataFixtures\ORM\Record\LoadVideos;
use Tests\DataFixtures\ORM\Record\Tidings\LoadNumberedTidings;
use Tests\Functional\TestCase;

/**
 * @group record-view
 */
class RecordViewFactoryTest extends TestCase
{
    private ?RecordViewFactory $recordViewFactory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->recordViewFactory = $this->getContainer()->get(RecordViewFactory::class);
    }

    protected function tearDown(): void
    {
        unset($this->recordViewFactory);

        parent::tearDown();
    }

    public function testFactoryCantCreateViewForUndefinedRecordSubType(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->recordViewFactory->create($this->createMock(Record::class));
    }

    public function testFactoryCanCreateArticleView(): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadArticles::class,
        ])->getReferenceRepository();

        /** @var Article $article */
        $article = $referenceRepository->getReference(LoadArticles::getRandReferenceName());

        $actualView = $this->recordViewFactory->create($article);

        $this->assertInstanceOf(ArticleView::class, $actualView);
    }

    public function testFactoryCanCreateGalleryView(): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadGallery::class,
        ])->getReferenceRepository();

        /** @var Gallery $gallery */
        $gallery = $referenceRepository->getReference(LoadGallery::getRandReferenceName());

        $actualView = $this->recordViewFactory->create($gallery);

        $this->assertInstanceOf(GalleryView::class, $actualView);
    }

    public function testFactoryCanCreateMapView(): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadMaps::class,
        ])->getReferenceRepository();

        /** @var Map $map */
        $map = $referenceRepository->getReference(LoadMaps::getRandReferenceName());

        $actualView = $this->recordViewFactory->create($map);

        $this->assertInstanceOf(MapView::class, $actualView);
    }

    public function testFactoryCanCreateNewsView(): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadNews::class,
        ])->getReferenceRepository();

        /** @var News $news */
        $news = $referenceRepository->getReference(LoadNews::getRandReferenceName());

        $actualView = $this->recordViewFactory->create($news);

        $this->assertInstanceOf(NewsView::class, $actualView);
    }

    public function testFactoryCanCreateTackleView(): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadTackles::class,
        ])->getReferenceRepository();

        /** @var Tackle $tackle */
        $tackle = $referenceRepository->getReference(LoadTackles::getRandReferenceName());

        $actualView = $this->recordViewFactory->create($tackle);

        $this->assertInstanceOf(TackleView::class, $actualView);
    }

    public function testFactoryCanCreateTackleReviewView(): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadTackleReviews::class,
        ])->getReferenceRepository();

        /** @var TackleReview $tackleReview */
        $tackleReview = $referenceRepository->getReference(LoadTackleReviews::getRandReferenceName());

        $actualView = $this->recordViewFactory->create($tackleReview);

        $this->assertInstanceOf(TackleReviewView::class, $actualView);
    }

    public function testFactoryCanCreateTidingsView(): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadNumberedTidings::class,
        ])->getReferenceRepository();

        /** @var TackleReview $tidings */
        $tidings = $referenceRepository->getReference(LoadNumberedTidings::getRandReferenceName());

        $actualView = $this->recordViewFactory->create($tidings);

        $this->assertInstanceOf(TidingsView::class, $actualView);
    }

    public function testFactoryCanCreateVideoView(): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadVideos::class,
        ])->getReferenceRepository();

        /** @var Video $video */
        $video = $referenceRepository->getReference(LoadVideos::getRandReferenceName());

        $actualView = $this->recordViewFactory->create($video);

        $this->assertInstanceOf(VideoView::class, $actualView);
    }

    public function testFactoryCanCreateCompanyArticleView(): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadPaidReservoirsCompanyArticle::class,
        ])->getReferenceRepository();

        /** @var CompanyArticle $companyArticle */
        $companyArticle = $referenceRepository->getReference(LoadPaidReservoirsCompanyArticle::REFERENCE_NAME);

        $actualView = $this->recordViewFactory->create($companyArticle);

        $this->assertInstanceOf(CompanyArticleView::class, $actualView);
    }
}
