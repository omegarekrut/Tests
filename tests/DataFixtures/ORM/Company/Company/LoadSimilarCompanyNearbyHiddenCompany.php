<?php

namespace Tests\DataFixtures\ORM\Company\Company;

use App\Domain\Company\Entity\Company;
use App\Domain\User\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Tests\DataFixtures\Helper\Factory\CompanyFactory;
use Tests\DataFixtures\Helper\Factory\ContactDTOFakeFactory;
use Tests\DataFixtures\ORM\Company\Rubric\LoadAquaMotorcycleShopsRubric;
use Tests\DataFixtures\ORM\User\LoadTestUser;

class LoadSimilarCompanyNearbyHiddenCompany extends Fixture implements DependentFixtureInterface
{
    public const REFERENCE_NAME = 'similar-company-nearby-hidden-company';

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
        /** @var User $userTest */
        $userTest = $this->getReference(LoadTestUser::USER_TEST);

        $company = $this->createCompany();

        $company->setOwner($userTest);
        $company->updateDescription('Похожая компания рядом со скрытой компанией');
        $company->rewriteContactsFromDTO($this->contactDTOFakeFactory->createFakeContactDTO());

        $manager->persist($company);
        $manager->flush();

        $this->addReference(self::REFERENCE_NAME, $company);
    }

    private function createCompany(): Company
    {
        $name = 'Похожая компания рядом со скрытой компанией';
        $rubric = $this->getReference(LoadAquaMotorcycleShopsRubric::REFERENCE_NAME);
        $rubrics = new ArrayCollection([$rubric]);

        return $this->companyFactory->createCompany($name, $rubrics);
    }

    /**
     * @return string[]
     */
    public function getDependencies(): array
    {
        return [
            LoadAquaMotorcycleShopsRubric::class,
            LoadTestUser::class,
        ];
    }
}
