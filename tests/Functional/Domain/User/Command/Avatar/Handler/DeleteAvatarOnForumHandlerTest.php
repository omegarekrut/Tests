<?php

namespace Tests\Functional\Domain\User\Command\Avatar\Handler;

use App\Bridge\Xenforo\ForumApiInterface;
use App\Domain\User\Command\Avatar\DeleteAvatarOnForumCommand;
use App\Domain\User\Entity\User;
use Tests\DataFixtures\ORM\User\LoadUserWithAvatar;
use Tests\Functional\TestCase;

class DeleteAvatarOnForumHandlerTest extends TestCase
{
    public function testHandle(): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadUserWithAvatar::class,
        ])->getReferenceRepository();

        /** @var User $user */
        $user = $referenceRepository->getReference(LoadUserWithAvatar::REFERENCE_NAME);

        $forumClient = $this->getContainer()->get(ForumApiInterface::class);

        $command = new DeleteAvatarOnForumCommand($user);
        $this->getCommandBus()->handle($command);

        $this->assertTrue($forumClient->profile()->isAvatarDeleted());
    }
}
