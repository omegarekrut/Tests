<?php

namespace Tests\Functional\Domain\User\Command\Avatar\Handler;

use App\Bridge\Xenforo\ForumApiInterface;
use App\Domain\User\Command\Avatar\UploadAvatarCommand;
use App\Domain\User\Entity\User;
use App\Util\ImageStorage\ImageStorageClientInterface;
use App\Util\ImageStorage\ImageStorageClientMock;
use App\Util\ImageStorage\TransferObject\CroppableImageTransferObject;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Tests\DataFixtures\ORM\User\LoadUserWithAvatar;
use Tests\Functional\TestCase;

class UploadAvatarHandlerTest extends TestCase
{
    private User $user;
    private ImageStorageClientMock $imageStorageClient;
    private UploadAvatarCommand $command;
    private ForumApiInterface $forumClient;

    protected function setUp(): void
    {
        parent::setUp();

        $referenceRepository = $this->loadFixtures([
            LoadUserWithAvatar::class,
        ])->getReferenceRepository();

        $this->user = $referenceRepository->getReference(LoadUserWithAvatar::REFERENCE_NAME);

        $this->imageStorageClient = $this->getContainer()->get(ImageStorageClientInterface::class);
        $this->forumClient = $this->getContainer()->get(ForumApiInterface::class);

        $this->command = new UploadAvatarCommand($this->user);
        $this->command->croppableImage = new CroppableImageTransferObject();
        $this->command->croppableImage->imageFile = new UploadedFile(
            sprintf('%s/image20x29.jpeg', $this->getDataFixturesFolder()),
            'image20x29.jpeg',
            null,
            100,
            0,
            true
        );
        $this->command->croppableImage->sourceImageWidth = 20;
        $this->command->croppableImage->rotationAngle = 0;
        $this->command->croppableImage->croppingParameters = ['x0' => 0, 'y0' => 0, 'x1' => 10, 'y1' => 10];
    }

    protected function tearDown(): void
    {
        unset(
            $this->user,
            $this->imageStorageClient,
            $this->forumClient,
            $this->command,
        );

        parent::tearDown();
    }

    public function testAfterHandlingUserMustGetNewAvatar(): void
    {
        $oldAvatar = $this->user->getAvatar();

        $this->getCommandBus()->handle($this->command);

        $this->assertNotEmpty($this->user->getAvatar());
        $this->assertFalse($oldAvatar === $this->user->getAvatar());
    }

    public function testOldUserAvatarImageMustBeDeletedInStorage(): void
    {
        $oldUserAvatarImage = $this->user->getAvatar()->getImage();

        $this->getCommandBus()->handle($this->command);

        $deleteImages = $this->imageStorageClient->getDeletedImagesAndClear();

        $this->assertCount(1, $deleteImages);

        $deleteImage = current($deleteImages);

        $this->assertEquals($oldUserAvatarImage, $deleteImage);
    }

    public function testAlsoAvatarMustBeUploadedToForum(): void
    {
        $this->getCommandBus()->handle($this->command);

        $this->assertNotEmpty($this->forumClient->profile()->getUploadedAvatar());
    }
}
