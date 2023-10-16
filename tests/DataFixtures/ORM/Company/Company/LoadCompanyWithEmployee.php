<?php

namespace Tests\DataFixtures\ORM\Company\Company;

use App\Domain\Company\Entity\Company;
use App\Domain\User\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Tests\DataFixtures\Helper\Factory\CompanyFactory;
use Tests\DataFixtures\ORM\Company\Rubric\LoadDefaultRubric;
use Tests\DataFixtures\ORM\User\LoadUserWhichCompanyEmployee;
use Tests\DataFixtures\ORM\User\LoadUserWithAvatar;

class LoadCompanyWithEmployee extends Fixture implements DependentFixtureInterface
{
    public const REFERENCE_NAME = 'company-with-employee';

    private CompanyFactory $companyFactory;

    public function __construct(CompanyFactory $companyFactory)
    {
        $this->companyFactory = $companyFactory;
    }

    public function load(ObjectManager $manager): void
    {
        $user = $this->getReference(LoadUserWhichCompanyEmployee::REFERENCE_NAME);
        assert($user instanceof User);

        $owner = $this->getReference(LoadUserWithAvatar::REFERENCE_NAME);
        assert($owner instanceof User);

        $companyWithEmployee = $this->createCompany();
        $companyWithEmployee->setOwner($owner);

        $companyWithEmployee->addEmployee($user);

        $manager->persist($companyWithEmployee);
        $manager->flush();

        $this->addReference(self::REFERENCE_NAME, $companyWithEmployee);
    }

    private function createCompany(): Company
    {
        $name = self::REFERENCE_NAME;
        $rubric = $this->getReference(LoadDefaultRubric::REFERENCE_NAME);
        $rubrics = new ArrayCollection([$rubric]);

        return $this->companyFactory->createCompany($name, $rubrics);
    }

    /**
     * @return string[]
     */
    public function getDependencies(): array
    {
        return [
            LoadUserWhichCompanyEmployee::class,
            LoadDefaultRubric::class,
            LoadUserWithAvatar::class,
        ];
    }
}
