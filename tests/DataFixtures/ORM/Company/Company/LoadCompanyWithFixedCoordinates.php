<?php

namespace Tests\DataFixtures\ORM\Company\Company;

use App\Domain\Company\Entity\Company;
use App\Util\Security\AssertionSubject\OwnerInterface;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Tests\DataFixtures\Helper\Factory\CompanyFactory;
use Tests\DataFixtures\Helper\Factory\ContactDTOFakeFactory;
use Tests\DataFixtures\ORM\Company\Rubric\LoadTackleShopsRubric;
use Tests\DataFixtures\ORM\User\LoadTestUser;

class LoadCompanyWithFixedCoordinates extends Fixture implements DependentFixtureInterface
{
    public const REFERENCE_NAME = 'company-with-fixed-coordinates';

    private CompanyFactory $companyFactory;
    private ContactDTOFakeFactory $contactDTOFakeFactory;

    public function __construct(
        CompanyFactory $companyFactory,
        ContactDTOFakeFactory $contactDTOFakeFactory
    ) {
        $this->companyFactory = $companyFactory;
        $this->contactDTOFakeFactory = $contactDTOFakeFactory;
    }

    public function load(ObjectManager $manager): void
    {
        /** @var OwnerInterface $companyOwner */
        $companyOwner = $this->getReference(LoadTestUser::USER_TEST);

        $company = $this->createCompany();

        $company->setOwner($companyOwner);
        $company->rewriteContactsFromDTO(
            $this->contactDTOFakeFactory->createFakeContactDTOWithFixedCoordinates()
        );

        $manager->persist($company);
        $manager->flush();

        $this->addReference(self::REFERENCE_NAME, $company);
    }

    private function createCompany(): Company
    {
        $name = self::REFERENCE_NAME;
        $rubric = $this->getReference(LoadTackleShopsRubric::REFERENCE_NAME);
        $rubrics = new ArrayCollection([$rubric]);

        return $this->companyFactory->createCompany($name, $rubrics);
    }

    /**
     * @return string[]
     */
    public function getDependencies(): array
    {
        return [
            LoadTestUser::class,
            LoadTackleShopsRubric::class,
        ];
    }
}
