<?php

namespace Tests\Unit\Domain\Record\Common\View\MosaicOfImages;

use App\Domain\Record\Common\View\MosaicOfImages\ImageForMosaic;
use App\Domain\Record\Common\View\MosaicOfImages\MosaicRowImagesLimiter;
use Generator;
use Tests\Unit\TestCase;

/**
 * @group mosaic-of-images
 */
class MosaicRowImagesLimiterTest extends TestCase
{
    public function testExpectedExceptionWithInvalidImages(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Array must contain only App\Domain\Record\Common\View\MosaicOfImages\ImageForMosaic items.');

        $mosaicRowImagesLimiter = new MosaicRowImagesLimiter();

        $mosaicRowImagesLimiter->takeMaxNumberOfImagesToFitMosaicRow(
            [(object) []],
            300,
            548,
            10
        );
    }

    /**
     * @param ImageForMosaic[] $images
     * @param ImageForMosaic[] $expectedImages
     *
     * @dataProvider imagesForMosaicProvider
     */
    public function testTakeMaxNumberOfImagesToFitMosaicRow(array $images, array $expectedImages): void
    {
        $mosaicRowImagesLimiter = new MosaicRowImagesLimiter();

        $imagesForMosaicRow = $mosaicRowImagesLimiter->takeMaxNumberOfImagesToFitMosaicRow(
            $images,
            300,
            548,
            10
        );

        $this->assertEquals($expectedImages, $imagesForMosaicRow);
    }

    public function imagesForMosaicProvider(): Generator
    {
        $imageWithWidth200 = new ImageForMosaic(
            'https://example.com/path-to-img/test1.jpg',
            200,
            300,
            'http://example.ru/path-to-record/#photoAlbum12345-1'
        );
        $imageWithWidth300 = new ImageForMosaic(
            'https://example.com/path-to-img/test2.jpg',
            300,
            300,
            'http://example.ru/path-to-record/#photoAlbum12345-2'
        );
        $imageWithWidth500 = new ImageForMosaic(
            'https://example.com/path-to-video-preview/test3.jpg',
            500,
            300,
            'http://example.ru/path-to-record/#photoAlbum12345-3'
        );
        $imageWithWidth600 = new ImageForMosaic(
            'https://example.com/path-to-video-preview/test4.jpg',
            600,
            300,
            'http://example.ru/path-to-record/#photoAlbum12345-4'
        );

        yield [
            [],
            [],
        ];

        yield [
            [$imageWithWidth200, $imageWithWidth300, $imageWithWidth500, $imageWithWidth600],
            [$imageWithWidth200, $imageWithWidth300, $imageWithWidth500],
        ];

        yield [
            [$imageWithWidth300, $imageWithWidth500, $imageWithWidth600, $imageWithWidth200],
            [$imageWithWidth300, $imageWithWidth500],
        ];

        yield [
            [$imageWithWidth600, $imageWithWidth200, $imageWithWidth300, $imageWithWidth500],
            [$imageWithWidth600],
        ];
    }
}
