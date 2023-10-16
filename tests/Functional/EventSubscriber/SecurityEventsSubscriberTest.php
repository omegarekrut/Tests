<?php

namespace Tests\Functional\EventSubscriber;

use App\Auth\Firewall\SuspiciousLogin\UserAuthorizationHistoryStorageInterface;
use App\Domain\Log\Entity\SuspiciousLoginLog;
use App\Domain\Log\Repository\SuspiciousLoginLogRepository;
use App\Domain\User\Entity\User;
use Doctrine\Common\DataFixtures\ReferenceRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Http\SecurityEvents;
use Tests\DataFixtures\ORM\User\LoadTestUser;
use Tests\DataFixtures\ORM\User\LoadUserWithDotInUsername;
use Tests\Functional\TestCase;

class SecurityEventsSubscriberTest extends TestCase
{
    /** @var ReferenceRepository */
    private $referenceRepository;
    /** @var User */
    private $testUser;
    /** @var User */
    private $userWithDotInUsername;
    /** @var UserAuthorizationHistoryStorageInterface */
    private $userAuthorizationHistoryStorage;
    /** @var SuspiciousLoginLogRepository */
    private $suspiciousLoginLogRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->referenceRepository = $this->loadFixtures([
            LoadTestUser::class,
            LoadUserWithDotInUsername::class,
        ])->getReferenceRepository();

        $this->testUser = $this->referenceRepository->getReference(LoadTestUser::USER_TEST);
        $this->userWithDotInUsername = $this->referenceRepository->getReference(LoadUserWithDotInUsername::REFERENCE_NAME);
        $this->userAuthorizationHistoryStorage = $this->getContainer()->get(UserAuthorizationHistoryStorageInterface::class);
        $this->suspiciousLoginLogRepository = $this->getEntityManager()->getRepository(SuspiciousLoginLog::class);
    }

    protected function tearDown(): void
    {
        unset(
            $this->referenceRepository,
            $this->testUser,
            $this->userWithDotInUsername,
            $this->userAuthorizationHistoryStorage,
            $this->suspiciousLoginLogRepository
        );

        parent::tearDown();
    }

    public function testSuspiciousUserMustBeInLog(): void
    {
        $this->userAuthorizationHistoryStorage->add($this->userWithDotInUsername);
        $testUserInteractiveLoginEvent = $this->createInteractiveLoginEventForUser($this->testUser);

        $this->getEventDispatcher()->dispatch(
            $testUserInteractiveLoginEvent,
            SecurityEvents::INTERACTIVE_LOGIN,
        );

        $suspiciousLoginLog = $this->suspiciousLoginLogRepository->findOneBy(['newUser' => $this->userWithDotInUsername->getId()]);

        $this->assertNotNull($suspiciousLoginLog);
        $this->assertInstanceOf(SuspiciousLoginLog::class, $suspiciousLoginLog);
        $this->assertEquals($this->testUser, $suspiciousLoginLog->getOldUser());
        $this->assertEquals($this->userWithDotInUsername, $suspiciousLoginLog->getNewUser());
    }

    public function testAfterLoginUserMustBeInAuthorizationHistoryStorage(): void
    {
        $testUserInteractiveLoginEvent = $this->createInteractiveLoginEventForUser($this->testUser);

        $this->getEventDispatcher()->dispatch(
            $testUserInteractiveLoginEvent,
            SecurityEvents::INTERACTIVE_LOGIN,
        );

        $this->assertTrue($this->testUser === $this->userAuthorizationHistoryStorage->getLatest());
    }

    private function createInteractiveLoginEventForUser(User $user): InteractiveLoginEvent
    {
        return new InteractiveLoginEvent(
            $this->createMock(Request::class),
            $this->createUsernamePasswordToken($user)
        );
    }

    private function createUsernamePasswordToken(User $user): UsernamePasswordToken
    {
        return new UsernamePasswordToken($user, $user->getPassword(), 'main', $user->getRoles());
    }
}
