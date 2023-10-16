<?php

namespace Tests\Unit\Auth\Firewall\SuspiciousLogin;

use App\Auth\Firewall\SuspiciousLogin\SuspiciousLoginChecker;
use App\Auth\Firewall\SuspiciousLogin\UserAuthorizationHistoryStorageInterface;
use App\Domain\Log\Event\SuspiciousUserLoginDetectedEvent;
use App\Domain\User\Entity\User;
use App\Domain\User\Entity\ValueObject\UserRole;
use Tests\Unit\Mock\EventDispatcherMock;
use Tests\Unit\TestCase;

class SuspiciousLoginCheckerTest extends TestCase
{
    private EventDispatcherMock $eventDispatcherMock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->eventDispatcherMock = new EventDispatcherMock();
    }

    protected function tearDown(): void
    {
        unset($this->eventDispatcherMock);

        parent::tearDown();
    }

    public function testCheckOnSuspiciousLoginBySingleUserAuthorization(): void
    {
        $user = $this->createCurrentAuthorizedUser();

        $suspiciousLoginChecker = new SuspiciousLoginChecker(
            $this->createUserAuthorizationHistoryStorage($user),
            $this->eventDispatcherMock,
        );

        $suspiciousLoginChecker->check($user);
        $suspiciousUserLoginDetectedEvent = $this->eventDispatcherMock->findLatestDispatchedEventByName(SuspiciousUserLoginDetectedEvent::class);

        $this->assertEquals(null, $suspiciousUserLoginDetectedEvent);
    }

    public function testCheckOnSuspiciousLoginByDifferentUsersAuthorization(): void
    {
        $currentAuthorizedUser = $this->createCurrentAuthorizedUser();
        $previousAuthorizedUser = $this->createPreviousAuthorizedUser();

        $suspiciousLoginChecker = new SuspiciousLoginChecker(
            $this->createUserAuthorizationHistoryStorage($previousAuthorizedUser),
            $this->eventDispatcherMock,
        );

        $suspiciousLoginChecker->check($currentAuthorizedUser);
        $suspiciousUserLoginDetectedEvent = $this->eventDispatcherMock->findLatestDispatchedEventByName(SuspiciousUserLoginDetectedEvent::class);
        $expectedDetectedEvent = new SuspiciousUserLoginDetectedEvent($currentAuthorizedUser, $previousAuthorizedUser);

        $this->assertEquals($expectedDetectedEvent, $suspiciousUserLoginDetectedEvent);
    }

    public function testCheckOnSuspiciousLoginByAdminAuthorizationAfterUser(): void
    {
        $adminUser = $this->createAdminUser();
        $previousAuthorizedUser = $this->createPreviousAuthorizedUser();

        $suspiciousLoginChecker = new SuspiciousLoginChecker(
            $this->createUserAuthorizationHistoryStorage($previousAuthorizedUser),
            $this->eventDispatcherMock,
        );

        $suspiciousLoginChecker->check($adminUser);
        $suspiciousUserLoginDetectedEvent = $this->eventDispatcherMock->findLatestDispatchedEventByName(SuspiciousUserLoginDetectedEvent::class);

        $this->assertEquals(null, $suspiciousUserLoginDetectedEvent);
    }

    public function testCheckOnSuspiciousLoginByUserAuthorizationAfterAdmin(): void
    {
        $adminUser = $this->createAdminUser();
        $currentAuthorizedUser = $this->createCurrentAuthorizedUser();

        $suspiciousLoginChecker = new SuspiciousLoginChecker(
            $this->createUserAuthorizationHistoryStorage($adminUser),
            $this->eventDispatcherMock,
        );

        $suspiciousLoginChecker->check($currentAuthorizedUser);
        $suspiciousUserLoginDetectedEvent = $this->eventDispatcherMock->findLatestDispatchedEventByName(SuspiciousUserLoginDetectedEvent::class);

        $this->assertEquals(null, $suspiciousUserLoginDetectedEvent);
    }

    public function testCheckOnSuspiciousLoginByUserFirstAuthorization(): void
    {
        $currentAuthorizedUser = $this->createCurrentAuthorizedUser();

        $suspiciousLoginChecker = new SuspiciousLoginChecker(
            $this->createUserAuthorizationHistoryStorage(null),
            $this->eventDispatcherMock,
        );

        $suspiciousLoginChecker->check($currentAuthorizedUser);
        $suspiciousUserLoginDetectedEvent = $this->eventDispatcherMock->findLatestDispatchedEventByName(SuspiciousUserLoginDetectedEvent::class);

        $this->assertEquals(null, $suspiciousUserLoginDetectedEvent);
    }

    private function createUserAuthorizationHistoryStorage(?User $user): UserAuthorizationHistoryStorageInterface
    {
        $userAuthorizationHistoryStorage = $this->createMock(UserAuthorizationHistoryStorageInterface::class);
        $userAuthorizationHistoryStorage
            ->method('getLatest')
            ->willReturn($user);

        return $userAuthorizationHistoryStorage;
    }

    private function createAdminUser(): User
    {
        return $this->createUser(3, 'adminUser', [(string) UserRole::admin()]);
    }

    private function createCurrentAuthorizedUser(): User
    {
        return $this->createUser(1, 'currentAuthorizedUser', [(string) UserRole::user()]);
    }

    private function createPreviousAuthorizedUser(): User
    {
        return $this->createUser(2, 'previousAuthorizedUser', [(string) UserRole::user()]);
    }

    /**
     * @param int $id
     * @param string $username
     * @param UserRole[]
     */
    private function createUser(int $id, string $username, array $roles): User
    {
        $stub = $this->createMock(User::class);
        $stub
            ->method('getId')
            ->willReturn($id);
        $stub
            ->method('getLogin')
            ->willReturn($username);
        $stub
            ->method('getRoles')
            ->willReturn($roles);

        return $stub;
    }
}
