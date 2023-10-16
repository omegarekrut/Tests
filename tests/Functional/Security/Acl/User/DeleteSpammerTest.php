<?php

namespace Tests\Functional\Security\Acl\User;

use App\Domain\User\Entity\User;
use Tests\DataFixtures\ORM\User\LoadNumberedUsers;
use Tests\DataFixtures\ORM\User\LoadOldSpammerUser;
use Tests\DataFixtures\ORM\User\LoadOldUsers;
use Tests\Functional\Security\Acl\AclTestCase;

/**
 * @group security
 */
class DeleteSpammerTest extends AclTestCase
{
    /**
     * @dataProvider getUserRoles
     */
    public function testDenyForAll(string $role): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadNumberedUsers::class,
        ])->getReferenceRepository();

        /** @var User $userNotFallingUnderRules */
        $userNotFallingUnderRules = $referenceRepository->getReference(LoadNumberedUsers::getRandReferenceName());

        $this->assertFalse($this->isAllowed($role, $this->createSubjectResource('user', $userNotFallingUnderRules), 'confirm_spammer'));
    }

    /**
     * @dataProvider getAdministrationRoles
     */
    public function testDenyDeleteOldUser(string $role): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadOldUsers::class,
        ])->getReferenceRepository();

        /** @var User $oldUser */
        $oldUser = $referenceRepository->getReference(LoadOldUsers::getRandReferenceName());

        $this->assertFalse($this->isAllowed($role, $this->createSubjectResource('user', $oldUser), 'confirm_spammer'));
    }

    /**
     * @dataProvider getAdministrationRoles
     */
    public function testDenyDeleteOldSpammer(string $role): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadOldSpammerUser::class,
        ])->getReferenceRepository();

        /** @var User $user */
        $user = $referenceRepository->getReference(LoadOldSpammerUser::REFERENCE_NAME);

        $this->assertFalse($this->isAllowed($role, $this->createSubjectResource('user', $user), 'confirm_spammer'));
    }

    /**
     * @dataProvider getUserRoles
     */
    public function testUnsupportedResource(string $role): void
    {
        $this->assertFalse($this->isAllowed($role, $this->createSubjectResource('user', null), 'confirm_spammer'));
    }

    /**
     * @dataProvider getAdministrationRoles
     */
    public function testUnsupportedResourceForAdministration(string $role): void
    {
        $this->assertTrue($this->isAllowed($role, $this->createSubjectResource('user', null), 'confirm_spammer'));
    }
}
