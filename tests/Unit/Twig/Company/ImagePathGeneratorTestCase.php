<?php

namespace Tests\Unit\Twig\Company;

use App\Twig\Company\ImagePathGenerator;
use App\Util\ImageStorage\CroppedImageInterface;
use App\Util\ImageStorage\ImageTransformerFactory;
use App\Util\ImageStorage\ValueObject\ImageCroppingParameters;
use Tests\Unit\TestCase;

class ImagePathGeneratorTestCase extends TestCase
{
    protected ImagePathGenerator $imagePathGenerator;

    protected function setUp(): void
    {
        parent::setUp();

        $imageFactory = new ImageTransformerFactory(
            'http://some-url.com',
            'key',
            'siteId'
        );

        $this->imagePathGenerator = new ImagePathGenerator($imageFactory);
    }

    /** @return array[][] */
    protected function getCroppingParametersCoordinates(ImageCroppingParameters $croppingParameters): array
    {
        return [
            $croppingParameters->getX0(),
            $croppingParameters->getY0(),
            $croppingParameters->getX1(),
            $croppingParameters->getY1(),
        ];
    }

    protected function getExpectedUrl(CroppedImageInterface $croppedImageContext): string
    {
        return sprintf(
            'http://some-url.com/siteId/%s__cr-%d-%d-%d-%d',
            $croppedImageContext->getImage()->getName(),
            ...$this->getCroppingParametersCoordinates($croppedImageContext->getCroppingParameters()),
        );
    }
}
