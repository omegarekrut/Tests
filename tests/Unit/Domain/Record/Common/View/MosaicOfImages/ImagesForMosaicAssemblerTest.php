<?php

namespace Tests\Unit\Domain\Record\Common\View\MosaicOfImages;

use App\Domain\Record\Common\View\MosaicOfImages\ImageForMosaic;
use App\Domain\Record\Common\View\MosaicOfImages\MosaicRowImagesLimiter;
use App\Domain\Record\Common\View\MosaicOfImages\ImagesForMosaicAssembler;
use App\Domain\Record\Common\View\VideoUrlView;
use App\Util\ImageStorage\Collection\ImageTransformerCollection;
use App\Util\ImageStorage\ImageTransformer;
use FastImageSize\FastImageSize;
use Tests\Unit\TestCase;

/**
 * @group mosaic-of-images
 */
class ImagesForMosaicAssemblerTest extends TestCase
{
    public function testAssembleImagesForMosaicWithoutPhotoAndVideoUrl(): void
    {
        $imagesForMosaicAssembler = $this->createImagesForMosaicAssembler();
        $imagesForMosaic = $imagesForMosaicAssembler->assemble(new ImageTransformerCollection([]), [], 12345);

        $this->assertEquals([], $imagesForMosaic);
    }

    public function testAssembleImagesForMosaicByOnlyPhotos(): void
    {
        $photos = [
            $this->createImageTransformer('test.jpg'),
            $this->createImageTransformer('test2.jpg'),
        ];

        $expectedImagesForMosaic = [
            new ImageForMosaic($photos[0], 540, 405, 'photoAlbum12345-1'),
            new ImageForMosaic($photos[1], 768, 432, 'photoAlbum12345-2'),
        ];

        $imagesForMosaicAssembler = $this->createImagesForMosaicAssembler();
        $imagesForMosaic = $imagesForMosaicAssembler->assemble(new ImageTransformerCollection($photos), [], 12345);

        $this->assertEquals($expectedImagesForMosaic, $imagesForMosaic);
    }

    public function testAssembleImagesForMosaicByOnlyVideos(): void
    {
        $videoUrlViews = [
            $this->createVideoUrlView('test.jpg'),
            $this->createVideoUrlView('test2.jpg'),
        ];

        $expectedImagesForMosaic = [
            new ImageForMosaic($videoUrlViews[0]->videoPreviewImagePath, 540, 405, 'videoAlbum12345-1'),
            new ImageForMosaic($videoUrlViews[1]->videoPreviewImagePath, 768, 432, 'videoAlbum12345-2'),
        ];

        $imagesForMosaicAssembler = $this->createImagesForMosaicAssembler();
        $imagesForMosaic = $imagesForMosaicAssembler->assemble(new ImageTransformerCollection([]), $videoUrlViews, 12345);

        $this->assertEquals($expectedImagesForMosaic, $imagesForMosaic);
    }

    public function testAssembleImagesForMosaicByTwoPhotosAndThoVideoUrlViews(): void
    {
        $photos = [
            $this->createImageTransformer('test.jpg'),
            $this->createImageTransformer('test2.jpg'),
        ];

        $videoUrlViews = [
            $this->createVideoUrlView('test.jpg'),
            $this->createVideoUrlView('test2.jpg'),
        ];

        $expectedImagesForMosaic = [
            new ImageForMosaic($photos[0], 540, 405, 'photoAlbum12345-1'),
            new ImageForMosaic($photos[1], 768, 432, 'photoAlbum12345-2'),
        ];

        $imagesForMosaicAssembler = $this->createImagesForMosaicAssembler();
        $imagesForMosaic = $imagesForMosaicAssembler->assemble(new ImageTransformerCollection($photos), $videoUrlViews, 12345);

        $this->assertEquals($expectedImagesForMosaic, $imagesForMosaic);
    }

    public function testAssembleImagesForMosaicByOnePhotoAndThoVideoUrlViews(): void
    {
        $photos = [
            $this->createImageTransformer('test.jpg'),
        ];

        $videoUrlViews = [
            $this->createVideoUrlView('test.jpg'),
            $this->createVideoUrlView('test2.jpg'),
        ];

        $expectedImagesForMosaic = [
            new ImageForMosaic($photos[0], 540, 405, 'photoAlbum12345-1'),
            new ImageForMosaic($videoUrlViews[0]->videoPreviewImagePath, 540, 405, 'videoAlbum12345-1'),
        ];

        $imagesForMosaicAssembler = $this->createImagesForMosaicAssembler();
        $imagesForMosaic = $imagesForMosaicAssembler->assemble(new ImageTransformerCollection($photos), $videoUrlViews, 12345);

        $this->assertEquals($expectedImagesForMosaic, $imagesForMosaic);
    }

    private function createImagesForMosaicAssembler(): ImagesForMosaicAssembler
    {
        return new ImagesForMosaicAssembler(
            new FastImageSize(),
            new MosaicRowImagesLimiter()
        );
    }

    private function createImageTransformer(string $imageName): ImageTransformer
    {
        $mock = $this->createMock(ImageTransformer::class);
        $mock->method('__toString')
            ->willReturn($this->getDataFixturesFolder().$imageName);

        $mockResize2Height = $this->createMock(ImageTransformer::class);
        $mockResize2Height->method('__toString')
            ->willReturn($this->getDataFixturesFolder().$imageName);

        $mock->method('withResize2Height')
            ->willReturn($mockResize2Height);

        return $mock;
    }

    private function createVideoUrlView(string $imageName): VideoUrlView
    {
        $videoUrlView = new VideoUrlView();

        $videoUrlView->videoPreviewImagePath = $this->getDataFixturesFolder().$imageName;
        $videoUrlView->url = 'http://example.ru/path-to-record/';

        return $videoUrlView;
    }
}
