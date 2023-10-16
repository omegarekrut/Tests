<?php

namespace Tests\DataFixtures\ORM\Company\Employee;

use App\Domain\Company\Entity\Company;
use App\Domain\User\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Tests\DataFixtures\ORM\Company\Company\LoadCompanyWithOwner;
use Tests\DataFixtures\ORM\User\LoadNumberedUsers;

final class LoadEmployeeEditor extends Fixture implements DependentFixtureInterface
{
    public const REFERENCE_NAME = 'employee-editor';

    public function load(ObjectManager $manager): void
    {
        $company = $this->getReference(LoadCompanyWithOwner::REFERENCE_NAME);
        assert($company instanceof Company);

        $user = $this->getReference(LoadNumberedUsers::getRandReferenceName());
        assert($user instanceof User);

        $company->addEmployee($user);

        $manager->persist($company);
        $manager->flush();

        $employee = $company->getEmployees()->first();

        $this->addReference(self::REFERENCE_NAME, $employee);
    }

    /**
     * @return string[]
     */
    public function getDependencies(): array
    {
        return [
            LoadCompanyWithOwner::class,
            LoadNumberedUsers::class,
        ];
    }
}
