<?php

namespace Tests\Functional\Domain\Company\Command;

use App\Domain\Company\Command\RemoveLogoCompanyCommand;
use App\Domain\Company\Entity\Company;
use App\Domain\Company\Entity\ValueObject\LogoImage;
use App\Util\ImageStorage\Image;
use Tests\Functional\ValidationTestCase;

class RemoveLogoCompanyCommandValidationTest extends ValidationTestCase
{
    public function testValidationShouldBePassedForCorrectFilledCommand(): void
    {
        $logoImage = $this->createLogoImageMock();
        $company = $this->createCompanyMockWithLogoImage($logoImage);

        $command = new RemoveLogoCompanyCommand($company);

        $this->getValidator()->validate($command);

        $this->assertEmpty($this->getValidator()->getLastErrors());
    }

    public function testWithUserWithoutAvatar(): void
    {
        $company = $this->createCompanyMockWithoutLogoImage();

        $command = new RemoveLogoCompanyCommand($company);

        $this->getValidator()->validate($command);

        $this->assertFieldInvalid('logoImage', 'Значение не должно быть null.');
    }

    private function createLogoImageMock(): LogoImage
    {
        $logoImage = $this->createMock(LogoImage::class);

        $logoImage->method('getImage')->willReturn(new Image('some logo'));

        return $logoImage;
    }

    private function createCompanyMockWithLogoImage(?LogoImage $logoImage): Company
    {
        $company = $this->createMock(Company::class);

        $company->method('getLogoImage')->willReturn($logoImage);

        return $company;
    }

    private function createCompanyMockWithoutLogoImage(): Company
    {
        return $this->createCompanyMockWithLogoImage(null);
    }
}
