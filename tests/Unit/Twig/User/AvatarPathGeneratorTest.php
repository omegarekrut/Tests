<?php

namespace Tests\Unit\Twig\User;

use App\Domain\User\Entity\User;
use App\Domain\User\Entity\ValueObject\Avatar;
use App\Twig\User\AvatarPathGenerator;
use App\Util\ImageStorage\Image;
use App\Util\ImageStorage\ImageTransformerFactory;
use App\Util\ImageStorage\ValueObject\ImageCroppingParameters;
use Tests\Unit\TestCase;

/**
 * @group twig
 */
class AvatarPathGeneratorTest extends TestCase
{
    private AvatarPathGenerator $avatarPathGenerator;

    protected function setUp(): void
    {
        parent::setUp();

        $imageFactory = new ImageTransformerFactory('http://some-url.com', 'key', 'siteId');
        $this->avatarPathGenerator = new AvatarPathGenerator($imageFactory);
    }

    public function testEmptyAvatar(): void
    {
        $this->assertEquals('/img/icon/user.svg', $this->avatarPathGenerator->withOriginalSide($this->createUser()));
    }

    public function testEmptyAvatarWithSmallSide(): void
    {
        $this->assertEquals('/img/icon/user.svg', $this->avatarPathGenerator->withSmallSide($this->createUser()));
    }

    public function testEmptyAvatarWithSmallSideFoMail(): void
    {
        $this->assertEquals('/img/icon/user.png', $this->avatarPathGenerator->withSmallSideForMail($this->createUser()));
    }

    public function testAvatarPath(): void
    {
        $user = $this->createUser(new Avatar(new Image('image.jpeg')));

        $avatarImage = $this->avatarPathGenerator->withOriginalSide($user);

        $this->assertEquals('http://some-url.com/siteId/image.jpeg?hash=7a15833a', $avatarImage);
    }

    public function testAvatarPathWithSmallSide(): void
    {
        $user = $this->createUser(new Avatar(new Image('image.jpeg')));

        $avatarImage = $this->avatarPathGenerator->withSmallSide($user);

        $this->assertEquals('http://some-url.com/siteId/image__rsf-50-50.jpeg?hash=f87d1eb8', $avatarImage);
    }

    public function testAvatarPathWithSmallSideForMail(): void
    {
        $user = $this->createUser(new Avatar(new Image('image.jpeg')));

        $avatarImage = $this->avatarPathGenerator->withSmallSideForMail($user);

        $this->assertEquals('http://some-url.com/siteId/image__rsf-50-50.jpeg?hash=f87d1eb8', $avatarImage);
    }

    public function testAvatarPathWithCrop(): void
    {
        $user = $this->createUser(new Avatar(new Image('image.jpeg'), new ImageCroppingParameters(10, 10, 50, 50)));

        $avatarImage = $this->avatarPathGenerator->withOriginalSide($user);

        $this->assertEquals('http://some-url.com/siteId/image__cr-10-10-50-50.jpeg?hash=a3ddd72d', $avatarImage);
    }

    public function testAvatarPathWithSmallSideAndCrop(): void
    {
        $user = $this->createUser(new Avatar(new Image('image.jpeg'), new ImageCroppingParameters(10, 10, 50, 50)));

        $avatarImage = $this->avatarPathGenerator->withSmallSide($user);

        $this->assertEquals('http://some-url.com/siteId/image__cr-10-10-50-50__rsf-50-50.jpeg?hash=c58662bc', $avatarImage);
    }

    public function testAvatarPathWithSmallSideAndCropForMail(): void
    {
        $user = $this->createUser(new Avatar(new Image('image.jpeg'), new ImageCroppingParameters(10, 10, 50, 50)));

        $avatarImage = $this->avatarPathGenerator->withSmallSideForMail($user);

        $this->assertEquals('http://some-url.com/siteId/image__cr-10-10-50-50__rsf-50-50.jpeg?hash=c58662bc', $avatarImage);
    }

    private function createUser(?Avatar $avatar = null): User
    {
        return $this->createConfiguredMock(User::class, [
            'getAvatar' => $avatar,
        ]);
    }
}
