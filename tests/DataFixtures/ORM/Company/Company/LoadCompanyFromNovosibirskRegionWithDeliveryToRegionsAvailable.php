<?php

namespace Tests\DataFixtures\ORM\Company\Company;

use App\Domain\Company\Collection\LocationCollection;
use App\Domain\Company\Entity\Company;
use App\Domain\Company\Entity\Location;
use App\Domain\Region\Entity\Region;
use App\Util\Coordinates\Coordinates;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Ramsey\Uuid\Uuid;
use Tests\DataFixtures\Helper\Factory\CompanyFactory;
use Tests\DataFixtures\Helper\Factory\ContactDTOFakeFactory;
use Tests\DataFixtures\ORM\Company\Rubric\LoadDefaultRubric;
use Tests\DataFixtures\ORM\Region\Region\LoadNovosibirskRegion;

class LoadCompanyFromNovosibirskRegionWithDeliveryToRegionsAvailable extends Fixture implements DependentFixtureInterface
{
    public const REFERENCE_NAME = 'company-with-novosibirsk-region-with-delivery-to-regions-avail';

    private ContactDTOFakeFactory $contactDTOFakeFactory;
    private CompanyFactory $companyFactory;

    public function __construct(ContactDTOFakeFactory $contactDTOFakeFactory, CompanyFactory $companyFactory)
    {
        $this->contactDTOFakeFactory = $contactDTOFakeFactory;
        $this->companyFactory = $companyFactory;
    }

    public function load(ObjectManager $manager): void
    {
        $region = $this->getReference(LoadNovosibirskRegion::REFERENCE_NAME);
        assert($region instanceof Region);

        $location = new Location(Uuid::uuid4(), new Coordinates(55.0415, 82.9346));
        $location->setRegion($region);

        $contactDTO = $this->contactDTOFakeFactory->createFakeContactDTO();
        $contactDTO->locations = new LocationCollection([$location]);
        $contactDTO->isDeliveryToRegionsAvailable = 1;

        $company = $this->createCompany();
        $company->rewriteContactsFromDTO($contactDTO);

        $manager->persist($company);
        $manager->flush();

        $this->addReference(self::REFERENCE_NAME, $company);
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
            LoadNovosibirskRegion::class,
            LoadDefaultRubric::class,
        ];
    }
}
