<?php

namespace Tests\Unit\Twig\Company;

use App\Domain\Company\Entity\ValueObject\BackgroundImage;
use App\Domain\Company\Entity\ValueObject\LogoImage;
use App\Util\ImageStorage\CroppedImageInterface;
use App\Util\ImageStorage\Image;
use App\Util\ImageStorage\ValueObject\ImageCroppingParameters;

/** @group twig */
class ImagePathGeneratorTest extends ImagePathGeneratorTestCase
{
    /** @dataProvider croppedImageContextProvider */
    public function testCropImageWithOriginalSide(CroppedImageInterface $cropBackgroundImageContext): void
    {
        $expectedUrl = $this->getExpectedUrl($cropBackgroundImageContext);

        $pathToImage = $this->imagePathGenerator->getPathWithOriginalSide($cropBackgroundImageContext);

        $this->assertStringContainsString(
            $expectedUrl,
            $pathToImage
        );
    }

    /** @dataProvider croppedImageContextWithSideProvider */
    public function testCropImageWithSpecificSide(
        CroppedImageInterface $cropBackgroundImageContext,
        int $side
    ): void {
        $expectedUrl = $this->getExpectedUrl($cropBackgroundImageContext);

        $pathToImage = $this->imagePathGenerator->getPathWithSpecificSide($cropBackgroundImageContext, $side);

        $this->assertStringContainsString(
            $expectedUrl,
            $pathToImage
        );

        $this->assertStringContainsString(
            sprintf('rsf-%d-%d', $side, $side),
            $pathToImage
        );
    }

    public function croppedImageContextProvider(): \Generator
    {
        yield [
            new BackgroundImage(
                new Image('background.jpeg'),
                new ImageCroppingParameters(10, 10, 50, 50)
            ),
        ];

        yield [
            new LogoImage(
                new Image('logo1.jpeg'),
                new ImageCroppingParameters(0, 0, 0, 0)
            ),
        ];

        yield [
            new LogoImage(
                new Image('background.jpeg'),
                new ImageCroppingParameters(0, 0, 0, 0)
            ),
        ];

        yield [
            new BackgroundImage(
                new Image('background.jpeg'),
                new ImageCroppingParameters(100000000000, 50000000, 25000000, 700000)
            ),
        ];
    }

    public function croppedImageContextWithSideProvider(): \Generator
    {
        yield [
            new BackgroundImage(
                new Image('background.jpeg'),
                new ImageCroppingParameters(10, 10, 50, 50)
            ),
            0,
        ];

        yield [
            new LogoImage(
                new Image('cool-logo.jpeg'),
                new ImageCroppingParameters(0, 0, 0, 0)
            ),
            40,
        ];

        yield [
            new BackgroundImage(
                new Image('background.jpeg'),
                new ImageCroppingParameters(100000000000, 50000000, 25000000, 700000)
            ),
            9999999999,
        ];
    }
}
