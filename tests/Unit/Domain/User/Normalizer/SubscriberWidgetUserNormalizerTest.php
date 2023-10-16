<?php

namespace Tests\Unit\Domain\User\Normalizer;

use App\Domain\User\Entity\User;
use App\Domain\User\Normalizer\SubscriberWidgetUserNormalizer;
use App\Twig\User\AvatarPathGenerator;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Tests\Unit\TestCase;

class SubscriberWidgetUserNormalizerTest extends TestCase
{
    public function testNormalize(): void
    {
        $user = $this->getUserMock(42, 'test');

        $normalizer = new SubscriberWidgetUserNormalizer($this->getUrlGenerator(), $this->getAvatarPathGenerator());

        $this->assertEquals(
            [
                'profile_link' => 'firstLink',
                'avatar_path_with_small_side' => 'firstAvatarLink',
                'login' => 'test',
            ],
            $normalizer->normalize($user),
        );
    }

    public function testNormalizeCollection(): void
    {
        $users = [
            $this->getUserMock(42, 'test'),
            $this->getUserMock(24, 'another'),
        ];

        $normalizer = new SubscriberWidgetUserNormalizer($this->getUrlGenerator(), $this->getAvatarPathGenerator());

        $this->assertEquals(
            [
                42 => [
                    'profile_link' => 'firstLink',
                    'avatar_path_with_small_side' => 'firstAvatarLink',
                    'login' => 'test',
                ],
                24 => [
                    'profile_link' => 'secondLink',
                    'avatar_path_with_small_side' => 'secondAvatarLink',
                    'login' => 'another',
                ],
            ],
            $normalizer->normalizeCollection($users),
        );
    }

    private function getUserMock(int $id, string $login): User
    {
        $user = $this->createMock(User::class);
        $user->method('getId')->willReturn($id);
        $user->method('getLogin')->willReturn($login);

        return $user;
    }

    private function getUrlGenerator(): UrlGeneratorInterface
    {
        $urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $urlGenerator->method('generate')
            ->willReturnOnConsecutiveCalls(
                'firstLink',
                'secondLink',
            );

        return $urlGenerator;
    }

    private function getAvatarPathGenerator(): AvatarPathGenerator
    {
        $avatarPathGenerator = $this->createMock(AvatarPathGenerator::class);
        $avatarPathGenerator->method('withSmallSide')
            ->willReturnOnConsecutiveCalls(
                'firstAvatarLink',
                'secondAvatarLink',
            );

        return $avatarPathGenerator;
    }
}
