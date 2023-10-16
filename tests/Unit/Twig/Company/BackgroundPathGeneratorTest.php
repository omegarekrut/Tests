<?php

namespace Tests\Unit\Twig\Company;

use App\Domain\Company\Entity\Company;
use App\Domain\Company\Entity\ValueObject\BackgroundImage;
use App\Twig\Company\BackgroundPathGenerator;
use App\Util\ImageStorage\Image;
use App\Util\ImageStorage\ValueObject\ImageCroppingParameters;

class BackgroundPathGeneratorTest extends ImagePathGeneratorTestCase
{
    private BackgroundPathGenerator $backgroundPathGenerator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->backgroundPathGenerator = new BackgroundPathGenerator($this->imagePathGenerator);
    }

    public function testGeneratingPathToCompanyWithBackgroundImage(): void
    {
        $backgroundImage = new BackgroundImage(
            new Image('bg.jpeg'),
            new ImageCroppingParameters(0, 0, 0, 0)
        );
        $companyWithBackgroundImage = $this->getCompanyWithBackgroundImage($backgroundImage);

        $expectUrl = $this->getExpectedUrl($companyWithBackgroundImage->getBackgroundImage());

        $backgroundImagePath = $this->backgroundPathGenerator->generatePathToCroppedBackgroundImage(
            $companyWithBackgroundImage
        );

        $this->assertStringContainsString($expectUrl, $backgroundImagePath);
    }

    public function testGeneratingPathToCompanyWithNullBackgroundImage(): void
    {
        $companyWithoutBackgroundImage = $this->getCompanyWithBackgroundImage(null);

        $expectUrl = '';

        $backgroundImagePath = $this->backgroundPathGenerator->generatePathToCroppedBackgroundImage(
            $companyWithoutBackgroundImage
        );

        $this->assertEquals($expectUrl, $backgroundImagePath);
    }

    private function getCompanyWithBackgroundImage(?BackgroundImage $backgroundImage): Company
    {
        $company = $this->createMock(Company::class);
        $company
            ->method('getBackgroundImage')
            ->willReturn($backgroundImage);

        return $company;
    }
}
