<?php

namespace Tests\Functional\Domain\User\Command\Avatar\Handler;

use App\Bridge\Xenforo\ForumApiInterface;
use App\Domain\User\Command\Avatar\UpdateAvatarOnForumCommand;
use App\Domain\User\Entity\User;
use App\Util\ImageStorage\ValueObject\ImageCroppingParameters;
use Tests\DataFixtures\ORM\User\LoadUserWithAvatar;
use Tests\Functional\TestCase;

class UpdateAvatarOnForumHandlerTest extends TestCase
{
    public function testHandle(): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadUserWithAvatar::class,
        ])->getReferenceRepository();

        /** @var User $user */
        $user = $referenceRepository->getReference(LoadUserWithAvatar::REFERENCE_NAME);

        $forumClient = $this->getContainer()->get(ForumApiInterface::class);

        $command = new UpdateAvatarOnForumCommand($user);
        $this->getCommandBus()->handle($command);

        $this->assertNotEmpty($forumClient->profile()->getUploadedAvatar());
        $this->assertEqualsCroppingParameters($user->getAvatar()->getCroppingParameters(), $forumClient->profile()->getUploadedAvatar());
    }

    private function assertEqualsCroppingParameters(ImageCroppingParameters $expectedCroupingParameters, string $imageUrl): void
    {
        $expectedCroupingParametersInUrl = sprintf(
            'cr-%d-%d-%d-%d',
            $expectedCroupingParameters->getX0(),
            $expectedCroupingParameters->getY0(),
            $expectedCroupingParameters->getX1(),
            $expectedCroupingParameters->getY1()
        );

        $this->assertStringContainsString($expectedCroupingParametersInUrl, $imageUrl);
    }
}
