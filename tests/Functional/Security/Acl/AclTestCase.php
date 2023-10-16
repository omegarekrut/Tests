<?php

namespace Tests\Functional\Security\Acl;

use App\Domain\User\Entity\ValueObject\UserRole;
use App\Util\Acl\SubjectResource;
use Tests\Functional\TestCase;
use Laminas\Permissions\Acl\AclInterface;
use Laminas\Permissions\Acl\Resource\ResourceInterface;

class AclTestCase extends TestCase
{
    private $acl;

    protected function tearDown(): void
    {
        unset($this->acl);

        parent::tearDown();
    }

    /** dataProvider */
    public function getUserRoles(): array
    {
        return [
            ['IS_AUTHENTICATED_ANONYMOUSLY'],
            [(string) UserRole::user()],
        ];
    }

    /** dataProvider */
    public function getAdministrationRoles(): \Generator
    {
        foreach (UserRole::getAdministrationRoles() as $administrationRole) {
            yield [(string) $administrationRole];
        }
    }

    /** dataProvider */
    public function getRoles(): array
    {
        return array_merge($this->getUserRoles(), $this->getAdministrationRoles());
    }

    protected function createSubjectResource(string $id, /** object */$subject = null): ResourceInterface
    {
        return new SubjectResource($id, $subject);
    }

    protected function isAllowed(string $role, ResourceInterface $resource, $privilege): bool
    {
        return $this->getAcl()->isAllowed($role, $resource, $privilege);
    }

    private function getAcl(): AclInterface
    {
        return $this->acl ?? $this->acl = $this->getContainer()->get('module.acl.acl');;
    }
}
