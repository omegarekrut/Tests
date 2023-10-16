<?php

namespace Tests\Unit\Util\Security\Assertion;

use App\Domain\Company\Entity\Company;
use App\Domain\Draft\Entity\Draft;
use App\Domain\Record\CompanyArticle\Entity\CompanyArticle;
use App\Util\Security\Assertion\OwnerOnly;
use Laminas\Permissions\Acl\Acl;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Tests\Unit\TestCase;

class OnlyOwnerAssertionTest extends TestCase
{
    use ResourceFactory;

    private OwnerOnly $ownerOnlyAssertion;
    private Subject $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = (new Subject())->setId(1);
        $this->ownerOnlyAssertion = new OwnerOnly($this->getTokenStorage($this->user));
    }

    protected function tearDown(): void
    {
        unset(
            $this->ownerOnlyAssertion,
            $this->user,
        );

        parent::tearDown();
    }

    /**
     * @param mixed $user
     */
    private function getTokenStorage($user): TokenStorage
    {
        $token = new UsernamePasswordToken(
            $user,
            'password',
            'main',
            []
        );
        $tokenStorage = new TokenStorage();
        $tokenStorage->setToken($token);

        return $tokenStorage;
    }

    public function testAccessGranted(): void
    {
        $resource = $this->createSubjectResource($this->user);
        $allow = $this->ownerOnlyAssertion->assert(new Acl(), null, $resource, 'update');

        $this->assertTrue($this->user->wasCalled('getOwner'));
        $this->assertTrue($allow);
    }

    public function testAccessDenied(): void
    {
        $newSubject = new Subject();
        $resource = $this->createSubjectResource($newSubject);

        $allow = $this->ownerOnlyAssertion->assert(new Acl(), null, $resource, 'update');
        $this->assertTrue($newSubject->wasCalled('getOwner'));
        $this->assertFalse($allow);
    }

    public function testUnsupportedResourceWithoutSubject(): void
    {
        $resourceWithoutSubject = $this->createNoSubjectResource();

        $this->expectExceptionMessage('Assertion OwnerOnly for resource is not supported. Resource should implement HasOwnerInterface');

        $this->ownerOnlyAssertion->assert(new Acl(), null, $resourceWithoutSubject, 'update');
    }

    public function testUnsupportedResourceWithInvalidInterface(): void
    {
        $resourceWithInvalidInterface = $this->createSubjectResource(null);

        $this->expectExceptionMessage('Assertion OwnerOnly for resource is not supported. Resource should implement HasOwnerInterface');

        $this->ownerOnlyAssertion->assert(new Acl(), null, $resourceWithInvalidInterface, 'update');
    }

    public function testUnsupportedResourceWithoutHasOwnerInterfaceImplementation(): void
    {
        $resourceWithoutHasOwnerInterfaceImplementation = $this->createSubjectResource($this->createMock(Draft::class));

        $this->expectExceptionMessage('Assertion OwnerOnly for resource is not supported. Resource should implement HasOwnerInterface');

        $this->ownerOnlyAssertion->assert(new Acl(), null, $resourceWithoutHasOwnerInterfaceImplementation, 'update');
    }

    public function testSupportedResourceWithCompany(): void
    {
        $resourceWithCompany = $this->createSubjectResource($this->createMock(Company::class));

        $assertion = $this->ownerOnlyAssertion->assert(new Acl(), null, $resourceWithCompany, 'example');

        $this->assertFalse($assertion);
    }

    public function testSupportedResourceWithCompanyArticle(): void
    {
        $resourceWithCompanyArticle = $this->createSubjectResource($this->createMock(CompanyArticle::class));

        $assertion = $this->ownerOnlyAssertion->assert(new Acl(), null, $resourceWithCompanyArticle, 'example');

        $this->assertFalse($assertion);
    }

    public function testUnsupportedToken(): void
    {
        $resource = $this->createSubjectResource($this->user);
        $ownerOnlyAssertion = new OwnerOnly($this->getTokenStorage('user'));
        $allow = $ownerOnlyAssertion->assert(new Acl(), null, $resource, 'update');

        $this->assertFalse($allow);
    }
}
