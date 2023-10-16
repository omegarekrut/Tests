<?php

namespace Tests\Functional\Domain\Company\Command;

use App\Domain\Company\Command\RemoveBackgroundCompanyCommand;
use App\Domain\Company\Entity\Company;
use App\Domain\Company\Entity\ValueObject\BackgroundImage;
use App\Util\ImageStorage\Image;
use Tests\Functional\ValidationTestCase;

class RemoveBackgroundCompanyCommandValidationTest extends ValidationTestCase
{
    public function testValidationShouldBePassedForCorrectFilledCommand(): void
    {
        $backgroundImage = $this->createBackgroundImageMock();
        $company = $this->createCompanyMockWithBackgroundImage($backgroundImage);

        $command = new RemoveBackgroundCompanyCommand($company);

        $this->getValidator()->validate($command);

        $this->assertEmpty($this->getValidator()->getLastErrors());
    }

    public function testWithUserWithoutAvatar(): void
    {
        $company = $this->createCompanyMockWithoutBackgroundImage();

        $command = new RemoveBackgroundCompanyCommand($company);

        $this->getValidator()->validate($command);

        $this->assertFieldInvalid('backgroundImage', 'Значение не должно быть null.');
    }

    private function createBackgroundImageMock(): BackgroundImage
    {
        $backgroundImage = $this->createMock(BackgroundImage::class);

        $backgroundImage->method('getImage')->willReturn(new Image('some background'));

        return $backgroundImage;
    }

    private function createCompanyMockWithBackgroundImage(?BackgroundImage $backgroundImage): Company
    {
        $company = $this->createMock(Company::class);

        $company->method('getBackgroundImage')->willReturn($backgroundImage);

        return $company;
    }

    private function createCompanyMockWithoutBackgroundImage(): Company
    {
        return $this->createCompanyMockWithBackgroundImage(null);
    }
}
