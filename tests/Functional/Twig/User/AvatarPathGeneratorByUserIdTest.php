<?php

namespace Tests\Functional\Twig\User;

use App\Domain\User\Entity\User;
use App\Twig\User\AvatarPathGeneratorByUserId;
use App\Twig\User\DefaultAvatarPath;
use Tests\DataFixtures\ORM\User\LoadUserWithAvatar;
use Tests\Functional\TestCase;

class AvatarPathGeneratorByUserIdTest extends TestCase
{
    private const DEFAULT_AVATAR_PATH = DefaultAvatarPath::NO_AVATAR_IMAGE;

    /**
     * @var AvatarPathGeneratorByUserId
     */
    private $avatarPathGenerator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->avatarPathGenerator = $this->getContainer()->get(AvatarPathGeneratorByUserId::class);
    }

    protected function tearDown(): void
    {
        unset($this->avatarPathGenerator);
    }

    public function testGenerateAvatarPathByExistingUserId(): void
    {
        $user = $this->loadUserWithAvatar();

        $avatarPath = ($this->avatarPathGenerator)($user->getId());

        $this->assertNotEquals(self::DEFAULT_AVATAR_PATH, $avatarPath);
    }

    public function testGenerateAvatarPathByNotExistingUserId(): void
    {
        $nonExistentUserId = 0;

        $avatarPath = ($this->avatarPathGenerator)($nonExistentUserId);

        $this->assertEquals(self::DEFAULT_AVATAR_PATH, $avatarPath);
    }

    private function loadUserWithAvatar(): User
    {
        $referenceRepository = $this->loadFixtures([
            LoadUserWithAvatar::class,
        ])->getReferenceRepository();

        return $referenceRepository->getReference(LoadUserWithAvatar::REFERENCE_NAME);
    }
}
