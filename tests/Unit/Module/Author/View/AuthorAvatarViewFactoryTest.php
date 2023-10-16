<?php

namespace Tests\Unit\Module\Author\View;

use App\Domain\Company\Entity\Company;
use App\Domain\User\Entity\User;
use App\Module\Author\View\AuthorAvatarViewFactory;
use App\Twig\Company\LogoPathGenerator;
use App\Twig\User\AvatarPathGenerator;
use App\Twig\User\DefaultAvatarPath;
use Tests\Unit\TestCase;

class AuthorAvatarViewFactoryTest extends TestCase
{
    protected AuthorAvatarViewFactory $authorAvatarViewFactory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->authorAvatarViewFactory = new AuthorAvatarViewFactory(
            $this->getAvatarPathGenerator(),
            $this->getDefaultUserLogoGenerator(),
            $this->getLogoPathGenerator()
        );
    }

    protected function tearDown(): void
    {
        unset($this->authorAvatarViewFactory);

        parent::tearDown();
    }

    public function testGenerateAuthorAvatarViewForAnonymousUser(): void
    {
        $avatar = $this->authorAvatarViewFactory->createForUserAnonymous();

        $this->assertEquals('link_to_image_default.jpeg', $avatar->withOriginalSide);
        $this->assertEquals('link_to_image_default.jpeg', $avatar->withSmallSideForMail);
        $this->assertEquals('link_to_image_default.jpeg', $avatar->withSmallSide);
    }

    public function testGenerateAuthorAvatarViewForUser(): void
    {
        $user = $this->createMock(User::class);

        $avatar = $this->authorAvatarViewFactory->createForUser($user);

        $this->assertEquals('link_to_image_with_original_side.jpeg', $avatar->withOriginalSide);
        $this->assertEquals('link_to_image_with_small_side_for_mail.jpeg', $avatar->withSmallSideForMail);
        $this->assertEquals('link_to_image_with_small_side.jpeg', $avatar->withSmallSide);
    }

    public function testGenerateAuthorAvatarViewForCompany(): void
    {
        $company = $this->createMock(Company::class);

        $avatar = $this->authorAvatarViewFactory->createForCompany($company);

        $this->assertEquals('link_to_image_cropped_logo_image_with_original_side.jpeg', $avatar->withOriginalSide);
        $this->assertEquals('link_to_image_cropped_logo_image_with_original_side.jpeg', $avatar->withSmallSideForMail);
        $this->assertEquals('link_to_image_cropped_logo_image_with_small_side.jpeg', $avatar->withSmallSide);
    }

    public function testGenerateAuthorAvatarViewForCompanyAnonymous(): void
    {
        $avatar = $this->authorAvatarViewFactory->createForCompanyAnonymous();

        $this->assertEquals(LogoPathGenerator::COMPANY_DEFAULT_LOGO_PATH, $avatar->withOriginalSide);
        $this->assertEquals(LogoPathGenerator::COMPANY_DEFAULT_LOGO_PATH, $avatar->withSmallSideForMail);
        $this->assertEquals(LogoPathGenerator::COMPANY_DEFAULT_LOGO_PATH, $avatar->withSmallSide);
    }

    private function getAvatarPathGenerator(): AvatarPathGenerator
    {
        $avatarPathGenerator = $this->createMock(AvatarPathGenerator::class);
        $avatarPathGenerator->method('withOriginalSide')
            ->willReturn('link_to_image_with_original_side.jpeg');

        $avatarPathGenerator->method('withSmallSide')
            ->willReturn('link_to_image_with_small_side.jpeg');

        $avatarPathGenerator->method('withSmallSideForMail')
            ->willReturn('link_to_image_with_small_side_for_mail.jpeg');

        return $avatarPathGenerator;
    }

    private function getLogoPathGenerator(): LogoPathGenerator
    {
        $logoPathGenerator = $this->createMock(LogoPathGenerator::class);

        $logoPathGenerator->method('generatePathToCroppedLogoImageWithOriginalSide')
            ->willReturn('link_to_image_cropped_logo_image_with_original_side.jpeg');

        $logoPathGenerator->method('generatePathToCroppedLogoImageWithSmallSide')
            ->willReturn('link_to_image_cropped_logo_image_with_small_side.jpeg');

        return $logoPathGenerator;
    }

    private function getDefaultUserLogoGenerator(): DefaultAvatarPath
    {
        $logoPathGenerator = $this->createMock(DefaultAvatarPath::class);

        $logoPathGenerator->method('__invoke')
            ->willReturn('link_to_image_default.jpeg');

        return $logoPathGenerator;
    }
}
