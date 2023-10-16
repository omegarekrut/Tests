<?php

namespace Tests\Functional\Domain\Record\Gallery\View;

use App\Domain\Record\Common\View\RecordViewMetadata;
use App\Domain\Record\Gallery\View\GalleryFromBestMonthViewFactory;
use App\Domain\Record\Gallery\View\GalleryView;
use App\Domain\Record\Gallery\View\GalleryViewFactory;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Tests\DataFixtures\ORM\Record\LoadGallery;
use Tests\Functional\TestCase;

/**
 * @group record-view
 */
class GalleryFromBestMonthViewFactoryTest extends TestCase
{
    private $galleryFromBestMonthViewFactory;

    protected function setUp(): void
    {
        parent::setUp();

        $defaultGalleryView = new GalleryView();
        $defaultGalleryView->metadata = new RecordViewMetadata();

        $galleryViewFactory = $this->createMock(GalleryViewFactory::class);
        $galleryViewFactory->method('create')
            ->willReturn($defaultGalleryView);

        $urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $urlGenerator->method('generate')
            ->willReturn('link-to-view-gallery-in-best-month-list');

        $this->galleryFromBestMonthViewFactory = new GalleryFromBestMonthViewFactory($galleryViewFactory, $urlGenerator);
    }

    protected function tearDown(): void
    {
        unset($this->galleryFromBestMonthViewFactory);

        parent::tearDown();
    }

    public function testChangeViewUrl(): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadGallery::class,
        ])->getReferenceRepository();

        $gallery = $referenceRepository->getReference(LoadGallery::getRandReferenceName());

        $galleryView = $this->galleryFromBestMonthViewFactory->create($gallery);

        $this->assertEquals('link-to-view-gallery-in-best-month-list', $galleryView->metadata->viewUrl);
    }
}
