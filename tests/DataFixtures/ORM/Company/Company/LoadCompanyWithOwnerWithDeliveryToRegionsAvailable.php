<?php

namespace Tests\DataFixtures\ORM\Company\Company;

use App\Domain\Company\Collection\LocationCollection;
use App\Domain\Company\Entity\Company;
use App\Domain\Company\Entity\Location;
use App\Domain\Region\Entity\Region;
use App\Util\Coordinates\Coordinates;
use App\Util\Security\AssertionSubject\OwnerInterface;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Ramsey\Uuid\Uuid;
use Tests\DataFixtures\Helper\Factory\CompanyFactory;
use Tests\DataFixtures\Helper\Factory\ContactDTOFakeFactory;
use Tests\DataFixtures\ORM\Company\Rubric\LoadDefaultRubric;
use Tests\DataFixtures\ORM\Region\Region\LoadIrkutskRegion;
use Tests\DataFixtures\ORM\User\LoadTestUser;

class LoadCompanyWithOwnerWithDeliveryToRegionsAvailable extends Fixture implements DependentFixtureInterface
{
    public const REFERENCE_NAME = 'company-with-owner-with-delivery-to-regions-available';

    private ContactDTOFakeFactory $contactDTOFakeFactory;
    private CompanyFactory $companyFactory;

    public function __construct(ContactDTOFakeFactory $contactDTOFakeFactory, CompanyFactory $companyFactory)
    {
        $this->contactDTOFakeFactory = $contactDTOFakeFactory;
        $this->companyFactory = $companyFactory;
    }

    public function load(ObjectManager $manager): void
    {
        /** @var OwnerInterface $userWhoWillOwnCompany */
        $userWhoWillOwnCompany = $this->getReference(LoadTestUser::USER_TEST);

        $companyWithOwner = $this->createCompany();
        $companyWithOwner->setOwner($userWhoWillOwnCompany);

        $manager->persist($companyWithOwner);
        $manager->flush();

        $this->addReference(self::REFERENCE_NAME, $companyWithOwner);
    }

    private function createCompany(): Company
    {
        $name = self::REFERENCE_NAME;

        $region = $this->getReference(LoadIrkutskRegion::REFERENCE_NAME);
        assert($region instanceof Region);

        $location = new Location(Uuid::uuid4(), new Coordinates(55.1, 105));
        $location->setRegion($region);

        $contactDTO = $this->contactDTOFakeFactory->createFakeContactDTO();
        $contactDTO->locations = new LocationCollection([$location]);
        $contactDTO->isDeliveryToRegionsAvailable = 1;

        $rubric = $this->getReference(LoadDefaultRubric::REFERENCE_NAME);
        $rubrics = new ArrayCollection([$rubric]);

        $company = $this->companyFactory->createCompany($name, $rubrics);
        $company->rewriteContactsFromDTO($contactDTO);

        return $company;
    }

    /**
     * @return string[]
     */
    public function getDependencies(): array
    {
        return [
            LoadIrkutskRegion::class,
            LoadTestUser::class,
            LoadDefaultRubric::class,
        ];
    }
}
