<?php

namespace Tests\Functional\Domain\User\Command\Avatar\Handler;

use App\Domain\User\Command\Avatar\DeleteAvatarFromStorageCommand;
use App\Domain\User\Entity\User;
use App\Util\ImageStorage\ImageStorageClientInterface;
use Tests\DataFixtures\ORM\User\LoadUserWithAvatar;
use Tests\Functional\TestCase;

class DeleteAvatarFromStorageHandlerTest extends TestCase
{
    public function testHandle(): void
    {
        $imageStorageClient = $this->getContainer()->get(ImageStorageClientInterface::class);

        $referenceRepository = $this->loadFixtures([
            LoadUserWithAvatar::class,
        ])->getReferenceRepository();

        /** @var User $user */
        $user = $referenceRepository->getReference(LoadUserWithAvatar::REFERENCE_NAME);
        $userAvatar = $user->getAvatar();

        $command = new DeleteAvatarFromStorageCommand($user);
        $this->getCommandBus()->handle($command);

        $deletedImages = $imageStorageClient->getDeletedImagesAndClear();

        $this->assertCount(1, $deletedImages);

        $deletedImage = current($deletedImages);

        $this->assertEquals($userAvatar->getImage(), $deletedImage);
    }
}
