<?php

namespace Tests\Functional\Domain\User\Command\Profile\Handler;

use App\Domain\User\Command\Avatar\DeleteAvatarFromStorageCommand;
use App\Domain\User\Command\Avatar\DeleteAvatarOnForumCommand;
use App\Domain\User\Command\Profile\DeleteUserAvatarCommand;
use App\Domain\User\Command\Profile\Handler\DeleteUserAvatarHandler;
use App\Domain\User\Entity\User;
use App\Domain\User\Repository\UserRepository;
use Tests\DataFixtures\ORM\User\LoadUserWithAvatar;
use Tests\Functional\TestCase;
use Tests\Unit\Mock\CommandBusMock;

class DeleteUserAvatarHandlerTest extends TestCase
{
    /** @var User */
    private $user;

    /** @var DeleteUserAvatarCommand */
    private $command;

    /** @var CommandBusMock */
    private $commandBus;

    /** @var DeleteUserAvatarHandler */
    private $handler;

    protected function setUp(): void
    {
        parent::setUp();

        $referenceRepository = $this->loadFixtures([
            LoadUserWithAvatar::class,
        ])->getReferenceRepository();

        $this->user = $referenceRepository->getReference(LoadUserWithAvatar::REFERENCE_NAME);
        $this->commandBus = new CommandBusMock();

        $this->command = new DeleteUserAvatarCommand($this->user);
        $this->handler = new DeleteUserAvatarHandler($this->commandBus, $this->createMock(UserRepository::class));
    }

    protected function tearDown(): void
    {
        unset(
            $this->user,
            $this->command,
            $this->commandBus,
            $this->handler
        );

        parent::tearDown();
    }

    public function testUserCanDeleteAvatar(): void
    {
        $this->getCommandBus()->handle($this->command);

        $this->assertEmpty($this->user->getAvatar());
    }

    public function testAvatarImageMustBeDeletedFromStorage(): void
    {
        $this->handler->handle($this->command);

        $this->assertTrue($this->commandBus->isHandled(DeleteAvatarFromStorageCommand::class));
    }

    public function testAvatarImageMustBeDeletedFromForum(): void
    {
        $this->handler->handle($this->command);

        $this->assertTrue($this->commandBus->isHandled(DeleteAvatarOnForumCommand::class));
    }
}
