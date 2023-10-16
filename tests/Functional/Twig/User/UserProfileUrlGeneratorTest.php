<?php

namespace Tests\Functional\Twig\User;

use App\Domain\User\Entity\User;
use App\Twig\User\UserProfileUrlGenerator;
use Tests\Functional\TestCase;

/**
 * @group twig
 */
class UserProfileUrlGeneratorTest extends TestCase
{
    public function testLinkShouldContainsUsernameAndProfileUrl(): void
    {
        $user = $this->createConfiguredMock(User::class, [
            'getId' => 42,
        ]);

        $urlGenerator = $this->getContainer()->get('router');
        $userProfileUrlGenerator = new UserProfileUrlGenerator($urlGenerator);
        $link = $userProfileUrlGenerator($user);

        $this->assertEquals('/users/profile/42/', $link);
    }
}
