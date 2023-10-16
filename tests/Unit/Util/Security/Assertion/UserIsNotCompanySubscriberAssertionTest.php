<?php

namespace Tests\Unit\Util\Security\Assertion;

use App\Domain\Company\Entity\Company;
use App\Domain\User\Entity\User;
use App\Util\Security\Assertion\UserIsNotCompanySubscriber;
use App\Util\Security\AssertionSubject\CompanyInterface;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Tests\Unit\TestCase;
use Laminas\Permissions\Acl\Acl;

class UserIsNotCompanySubscriberAssertionTest extends TestCase
{
    use ResourceFactory;

    private $userIsNotCompanySubscriber;
    private $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = $this->createMock(User::class);
        $this->userIsNotCompanySubscriber = new UserIsNotCompanySubscriber($this->getTokenStorage($this->user));
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->userIsNotCompanySubscriber);
    }

    public function testAccessGrantedWhereUserIsNotCompanyOwner(): void
    {
        $resource = $this->createSubjectResource($this->getCompanyWithoutOwnerMock());
        $allow = $this->userIsNotCompanySubscriber->assert(new Acl(), null, $resource);

        $this->assertTrue($allow);
    }

    public function testAccessGrantedWhereUserIsCompanyOwner(): void
    {
        $resource = $this->createSubjectResource($this->getCompanyWithOwnerMock());
        $allow = $this->userIsNotCompanySubscriber->assert(new Acl(), null, $resource);

        $this->assertTrue($allow);
    }

    public function testAccessGrantedWhereUserIsNotCompanySubscriber(): void
    {
        $this->user
            ->method('isCompanySubscriber')
            ->willReturn(false);

        $resource = $this->createSubjectResource($this->getCompanyWithoutOwnerMock());
        $allow = $this->userIsNotCompanySubscriber->assert(new Acl(), null, $resource);

        $this->assertTrue($allow);
    }

    public function testAccessDeniedWhereUserIsCompanySubscriber(): void
    {
        $this->user
            ->method('isCompanySubscriber')
            ->willReturn(true);

        $resource = $this->createSubjectResource($this->getCompanyWithoutOwnerMock());
        $allow = $this->userIsNotCompanySubscriber->assert(new Acl(), null, $resource);

        $this->assertFalse($allow);
    }

    private function getTokenStorage(User $user): TokenStorage
    {
        $token = new UsernamePasswordToken($user, 'password', 'main');
        $tokenStorage = new TokenStorage();
        $tokenStorage->setToken($token);

        return $tokenStorage;
    }

    private function getCompanyWithoutOwnerMock(): CompanyInterface
    {
        $stub = $this->createMock(Company::class);
        $stub
            ->method('getId')
            ->willReturn(Uuid::uuid4());

        return $stub;
    }

    private function getCompanyWithOwnerMock(): CompanyInterface
    {
        $stub = $this->createMock(Company::class);

        $stub
            ->method('getId')
            ->willReturn(Uuid::uuid4());
        $stub
            ->method('getOwner')
            ->willReturn($this->user);

        return $stub;
    }
}
