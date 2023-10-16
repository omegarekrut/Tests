<?php

namespace Tests\Unit\Twig\Company;

use App\Domain\Company\Entity\Company;
use App\Domain\Company\Entity\ValueObject\LogoImage;
use App\Twig\Company\LogoPathGenerator;
use App\Util\ImageStorage\Image;
use App\Util\ImageStorage\ValueObject\ImageCroppingParameters;

/** @group twig */
class LogoPathGeneratorTest extends ImagePathGeneratorTestCase
{
    private LogoPathGenerator $logoPathGenerator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->logoPathGenerator = new LogoPathGenerator($this->imagePathGenerator);
    }

    protected function tearDown(): void
    {
        unset($this->logoPathGenerator);

        parent::tearDown();
    }

    public function testGeneratingPathToLogoWithOriginSide(): void
    {
        $companyWithLogo = $this->createCompanyWithLogo();
        $expectUrl = $this->getExpectedUrl($companyWithLogo->getLogoImage());

        $logoImagePath = $this->logoPathGenerator->generatePathToCroppedLogoImageWithOriginalSide($companyWithLogo);

        $this->assertStringContainsString($expectUrl, $logoImagePath);
    }

    public function testGeneratingPathToNullLogoWithOriginSide(): void
    {
        $companyWithoutLogo = $this->createCompanyWithoutLogo();
        $expectUrl = LogoPathGenerator::COMPANY_DEFAULT_LOGO_PATH;

        $logoImagePath = $this->logoPathGenerator->generatePathToCroppedLogoImageWithOriginalSide($companyWithoutLogo);

        $this->assertStringContainsString($expectUrl, $logoImagePath);
    }

    public function testGeneratingPathToLogoWithSpecificSide(): void
    {
        $companyWithLogo = $this->createCompanyWithLogo();
        $expectUrl = $this->getExpectedUrl($companyWithLogo->getLogoImage());
        $side = LogoPathGenerator::SMALL_SIDE;

        $logoImagePath = $this->logoPathGenerator->generatePathToCroppedLogoImageWithSmallSide($companyWithLogo);

        $this->assertStringContainsString($expectUrl, $logoImagePath);
        $this->assertStringContainsString(
            sprintf('rsf-%d-%d', $side, $side),
            $logoImagePath
        );
    }

    public function testGeneratingPathToNullLogoWithSpecificSide(): void
    {
        $companyWithoutLogo = $this->createCompanyWithoutLogo();
        $expectUrl = LogoPathGenerator::COMPANY_DEFAULT_LOGO_PATH;

        $logoImagePath = $this->logoPathGenerator->generatePathToCroppedLogoImageWithSmallSide($companyWithoutLogo);

        $this->assertStringContainsString($expectUrl, $logoImagePath);
    }

    public function createCompanyWithLogo(): Company
    {
        $mockCompany = $this->createMock(Company::class);
        $mockCompany->method('getLogoImage')->willReturn(
            new LogoImage(
                new Image('logo1.jpeg'),
                new ImageCroppingParameters(0, 0, 0, 0)
            )
        );

        return $mockCompany;
    }

    public function createCompanyWithoutLogo(): Company
    {
        $mockCompany = $this->createMock(Company::class);
        $mockCompany->method('getLogoImage')->willReturn(
            null
        );

        return $mockCompany;
    }
}
