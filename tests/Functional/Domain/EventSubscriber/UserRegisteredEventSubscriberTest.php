<?php

namespace Tests\Functional\Domain\EventSubscriber;

use App\Auth\Visitor\Visitor;
use App\Domain\Ban\Service\BanInterface as BanServiceInterface;
use App\Domain\User\Command\UserRegistration\UserRegisterCommand;
use App\Domain\User\Entity\User;
use App\Domain\User\Event\UserRegisteredEvent;
use App\Module\SpamChecker\SpamUserChecker\StopForumSpamApiMock;
use Doctrine\Common\DataFixtures\ReferenceRepository;
use Resolventa\StopForumSpamApi\StopForumSpamApi;
use Tests\DataFixtures\ORM\User\LoadSpammerUser;
use Tests\DataFixtures\ORM\User\LoadTestUser;
use Tests\Functional\TestCase;

/**
 * @runTestsInSeparateProcesses @todo Cake under the hood re-initializes session
 * @preserveGlobalState disabled
 *
 * @group user-events
 */
class UserRegisteredEventSubscriberTest extends TestCase
{
    /** @var Visitor */
    private $visitor;
    /** @var StopForumSpamApiMock */
    private $stopForumSpamApi;
    /** @var ReferenceRepository */
    private $referenceRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->visitor = $this->getContainer()->get('visitor');
        $this->stopForumSpamApi = $this->getContainer()->get(StopForumSpamApi::class);

        $this->referenceRepository = $this->loadFixtures([
            LoadSpammerUser::class,
        ])->getReferenceRepository();
    }

    protected function tearDown(): void
    {
        unset(
            $this->visitor,
            $this->stopForumSpamApi
        );

        parent::tearDown();
    }

    public function testTrustedUserShouldBeLoggedAfterRegistration(): void
    {
        $registerUserCommand = $this->createUserRegistrationCommand(true);
        $user = $this->getCommandBus()->handle($registerUserCommand);

        $this->assertFalse($this->visitor->isGuest(), 'Authenticate trusted user');
        $this->assertEquals($user->getId(), $this->visitor->getUser()->getId());
    }

    public function testNotTrustedRegisteredUserShouldNotBeLogged(): void
    {
        $registerUserCommand = $this->createUserRegistrationCommand(false);
        $this->getCommandBus()->handle($registerUserCommand);

        $this->assertTrue($this->visitor->isGuest());
    }

    /**
     * @dataProvider getTrustedAndNotTrustedRegistrationCommands
     */
    public function testRegisteredUserShouldBeCheckedForSpam(UserRegisterCommand $registerUserCommand): void
    {
        $this->getCommandBus()->handle($registerUserCommand);

        $this->assertTrue($this->stopForumSpamApi->isGetCheckResponseCalled(), 'Check on StopForumSpam service is done');
    }

    public function getTrustedAndNotTrustedRegistrationCommands(): \Generator
    {
        yield [
            $this->createUserRegistrationCommand(false),
        ];

        yield [
            $this->createUserRegistrationCommand(true),
        ];
    }

    public function testSpammerUserAfterRegistrationMustBeBanned(): void
    {
        $spammerUser = $this->referenceRepository->getReference(LoadSpammerUser::REFERENCE_NAME);
        assert($spammerUser instanceof User);

        $this->stopForumSpamApi->useNegativeResponse();

        $this->getEventDispatcher()->dispatch(new UserRegisteredEvent($spammerUser));

        $banService = $this->getContainer()->get(BanServiceInterface::class);
        $isBanned = (bool) $banService->getBanInformationByUserId($spammerUser->getId());

        $this->assertTrue($isBanned);
    }

    public function testTrustedRegisteredUserShouldReceiveWelcomeEmail(): void
    {
        $registerUserCommand = $this->createUserRegistrationCommand(true);
        $user = $this->getCommandBus()->handle($registerUserCommand);

        $lastEmailSent = $this->loadLastEmailMessage();

        $this->assertStringContainsString('спасибо за регистрацию', $lastEmailSent, 'Welcome email is sent');
        $this->assertStringContainsString($user->getEmailAddress(), $lastEmailSent);
    }

    public function testNotTrustedRegisteredUserShouldReceiveConfirmationEmail(): void
    {
        $registerUserCommand = $this->createUserRegistrationCommand(false);
        $user = $this->getCommandBus()->handle($registerUserCommand);

        $lastEmailSent = $this->loadLastEmailMessage();

        $this->assertStringContainsString('confirm_email', $lastEmailSent, 'Confirmation email is sent');
        $this->assertStringContainsString($user->getEmailAddress(), $lastEmailSent);
    }

    private function createUserRegistrationCommand(bool $isTrustedUser): UserRegisterCommand
    {
        $registerUserCommand = new UserRegisterCommand($isTrustedUser);
        $registerUserCommand->username = 'trustedUser'.random_int(1, 999);
        $registerUserCommand->password = 'password';
        $registerUserCommand->email = 'email'.random_int(1, 999).'@email.com';

        return $registerUserCommand;
    }
}
