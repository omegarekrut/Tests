<?php

namespace Tests\Unit\Domain\User\Command\UserRegistration;

use App\Domain\User\Command\UserRegistration\Handler\UserRegisterHandler;
use App\Domain\User\Command\UserRegistration\RegisterUserOnForumCommand;
use App\Domain\User\Command\UserRegistration\UserRegisterCommand;
use App\Domain\User\Entity\UserFactory;
use App\Domain\User\Event\UserRegisteredEvent;
use App\Service\ClientIp;
use Tests\Unit\Mock\CommandBusMock;
use Tests\Unit\Mock\NullSaltGenerator;
use Tests\Unit\Mock\UserPasswordEncoderMock;
use Tests\Unit\Mock\EventDispatcherMock;
use Tests\Unit\Mock\ObjectManagerMock;
use Tests\Unit\TestCase;

/**
 * @group registration
 */
class UserRegisterHandlerTest extends TestCase
{
    /** @var UserRegisterCommand */
    private $command;
    /** @var UserFactory */
    private $userFactory;
    /** @var ObjectManagerMock */
    private $objectManager;
    /** @var CommandBusMock */
    private $commandBus;
    /** @var EventDispatcherMock */
    private $eventDispatcher;
    /** @var UserRegisterHandler */
    private $userRegisterHandler;

    protected function setUp(): void
    {
        parent::setUp();

        $clientIp = $this->createMock(ClientIp::class);
        $clientIp
            ->method('getIp')
            ->willReturn('127.0.0.1');

        $this->userFactory = new UserFactory($clientIp, new UserPasswordEncoderMock(), new NullSaltGenerator());
        $this->objectManager = new ObjectManagerMock();
        $this->commandBus = new CommandBusMock();
        $this->eventDispatcher = new EventDispatcherMock();

        $this->command = new UserRegisterCommand();
        $this->command->username = 'username';
        $this->command->password = 'password';
        $this->command->email = 'email@email.com';
        $this->command->isAgreedToNewsLetter = false;

        $this->userRegisterHandler = new UserRegisterHandler(
            $this->objectManager,
            $this->commandBus,
            $this->eventDispatcher,
            $this->userFactory
        );
    }

    protected function tearDown(): void
    {
        unset (
            $this->userFactory,
            $this->objectManager,
            $this->commandBus,
            $this->eventDispatcher,
            $this->command,
            $this->userRegisterHandler
        );

        parent::tearDown();
    }

    public function testAfterHandlingUserMustBeAddedToObjectManager(): void
    {
        $this->userRegisterHandler->handle($this->command);

        $persistedUser = $this->objectManager->getLastPersistedObject();

        $this->assertNotEmpty($persistedUser);
        $this->assertEquals($this->command->username, $persistedUser->getUsername());
        $this->assertEquals($this->command->password, $persistedUser->getPassword());
        $this->assertEquals($this->command->email, $persistedUser->getEmail());
        $this->assertEquals($this->command->isAgreedToNewsLetter, $persistedUser->isSubscribedToWeeklyNewsletter());
    }

    public function testUserMustBeAlsoCreatedInForum(): void
    {
        $this->userRegisterHandler->handle($this->command);

        $handledCommand = $this->commandBus->getLastHandledCommand();

        $this->assertNotEmpty($handledCommand);
        $this->assertInstanceOf(RegisterUserOnForumCommand::class, $handledCommand);
    }

    public function testEventMustBeThrowsAfterSuccessHandling(): void
    {
        $this->userRegisterHandler->handle($this->command);

        $dispatchedEvents = $this->eventDispatcher->getDispatchedEvents();
        $lastDispatchedEvent = $dispatchedEvents[UserRegisteredEvent::class][0] ?? null;

        $this->assertNotEmpty($lastDispatchedEvent);
        $this->assertInstanceOf(UserRegisteredEvent::class, $lastDispatchedEvent);
    }
}
