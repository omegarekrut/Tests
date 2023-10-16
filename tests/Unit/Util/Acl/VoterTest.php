<?php

namespace Tests\Unit\Util\Acl;

use App\Util\Acl\Voter as AclVoter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\Role\RoleHierarchy;
use Tests\Unit\TestCase;
use Laminas\Permissions\Acl\Acl;
use Laminas\Permissions\Acl\Assertion\AssertionInterface;
use Laminas\Permissions\Acl\Resource\GenericResource;
use Laminas\Permissions\Acl\Role\GenericRole;

class VoterTest extends TestCase
{
    /**
     * @var AclVoter
     */
    private $aclVoter;

    /**
     * @var Acl
     */
    private $acl;

    /**
     * @var TokenInterface
     */
    private $token;

    protected function setUp(): void
    {
        parent::setUp();

        $roleHierarchy = new RoleHierarchy([]);
        $this->acl = new Acl();
        $this->acl
            ->addRole(new GenericRole('ROLE_USER'))
            ->addResource(new GenericResource('comment'))
        ;

        $this->aclVoter = new AclVoter($this->acl, $roleHierarchy);
        $this->token = $this->getUserTokenInstance();
    }

    private function getUserTokenInstance(): TokenInterface
    {
        $token = new UsernamePasswordToken(
            'user_name',
            'password',
            'main',
            [
                'ROLE_USER',
            ]
        );

        return $token;
    }

    public function testAllowPermission()
    {
        $this->acl->allow('ROLE_USER', 'comment', 'unsubscribe');

        $allow = $this->aclVoter->vote($this->token, null, ['comment:unsubscribe']);
        $this->assertEquals(VoterInterface::ACCESS_GRANTED, $allow);
    }

    public function testDenyPermission()
    {
        $this->acl
            ->allow('ROLE_USER')
            ->deny('ROLE_USER', 'comment', 'unsubscribe')
        ;

        $allow = $this->aclVoter->vote($this->token, null, ['comment:unsubscribe']);
        $this->assertEquals(VoterInterface::ACCESS_DENIED, $allow);
    }

    public function testFullResource()
    {
        $this->acl->allow('ROLE_USER', 'comment');

        $allow = $this->aclVoter->vote($this->token, null, ['comment']);
        $this->assertEquals(VoterInterface::ACCESS_GRANTED, $allow);

    }

    public function testFullResourceWithoutChild()
    {
        $this->acl
            ->allow('ROLE_USER', 'comment')
            ->deny('ROLE_USER', 'comment', 'complain')
        ;

        $allow = $this->aclVoter->vote($this->token, null, ['comment']);
        $this->assertEquals(VoterInterface::ACCESS_DENIED, $allow);

    }

    public function testAbstain()
    {
        $this->acl->allow('ROLE_USER');

        $allow = $this->aclVoter->vote($this->token, null, ['ROLE_USER']);
        $this->assertEquals(VoterInterface::ACCESS_ABSTAIN, $allow);
    }

    public function testSubjectResource()
    {
        $resource = null;
        $assertion = $this->getAssertionMock($resource);
        $subject = 'subject';
        $this->acl->allow('ROLE_USER', null, null, $assertion);
        $this->aclVoter->vote($this->token, $subject, ['comment']);

        $this->assertEquals($subject, $resource->getResourceSubject());
        $this->assertEquals('comment', $resource->getResourceId());
    }

    private function getAssertionMock(&$resourceCome)
    {
        $stub = $this->createMock(AssertionInterface::class);
        $stub
            ->method('assert')
            ->will($this->returnCallback(function ($acl, $role = null, $resource = null, $privilege = null) use (&$resourceCome) {
                $resourceCome = $resource;
                return true;
            }));
        ;

        return $stub;
    }

    public function testCleanRolesInHierarchyBranch()
    {
        $roleHierarchy = new RoleHierarchy([
            'ROLE_ADMIN' => [
                'ROLE_MODERATOR',
            ],
            'ROLE_SUPER_USER' => [
                'ROLE_USER',
                'ROLE_FOO_USER',
            ],
        ]);

        $verifiableRoles = [];
        $acl = $this->getAclMock($verifiableRoles);
        $aclVoter = new AclVoter($acl, $roleHierarchy);
        $token = new UsernamePasswordToken(
            'user',
            'password',
            'main',
            [
                'ROLE_ADMIN',
                'ROLE_MODERATOR',
                'ROLE_USER',
                'ROLE_SUPER_USER',
                'ROLE_FOO_USER',
            ]
        );

        $aclVoter->vote($token, null, ['comment']);

        $this->assertEquals([
            'ROLE_ADMIN',
            'ROLE_SUPER_USER',
        ], $verifiableRoles);
    }

    private function getAclMock(array &$verifiableRoles): Acl
    {
        $stub = $this->createMock(Acl::class);
        $stub
            ->method('isAllowed')
            ->will($this->returnCallback(function ($role, $resource, $privilege) use (&$verifiableRoles) {
                $verifiableRoles[] = $role;

                return false;
            }))
        ;

        return $stub;
    }
}
