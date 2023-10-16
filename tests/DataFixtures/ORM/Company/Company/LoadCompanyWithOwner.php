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
use Tests\DataFixtures\ORM\Region\Region\LoadNovosibirskRegion;
use Tests\DataFixtures\ORM\SingleReferenceFixtureInterface;
use Tests\DataFixtures\ORM\User\LoadTestUser;

class LoadCompanyWithOwner extends Fixture implements SingleReferenceFixtureInterface, DependentFixtureInterface
{
    /** @deprecated Use {@link getReferenceName} */
    public const REFERENCE_NAME = 'company-with-owner';

    private ContactDTOFakeFactory $contactDTOFakeFactory;
    private CompanyFactory $companyFactory;

    public function __construct(ContactDTOFakeFactory $contactDTOFakeFactory, CompanyFactory $companyFactory)
    {
        $this->contactDTOFakeFactory = $contactDTOFakeFactory;
        $this->companyFactory = $companyFactory;
    }

    public static function getReferenceName(): string
    {
        return self::REFERENCE_NAME;
    }

    public function load(ObjectManager $manager): void
    {
        /** @var OwnerInterface $userWhoWillOwnCompany */
        $userWhoWillOwnCompany = $this->getReference(LoadTestUser::USER_TEST);

        $companyWithOwner = $this->createCompany();
        $companyWithOwner->setOwner($userWhoWillOwnCompany);

        $manager->persist($companyWithOwner);
        $manager->flush();

        $this->addReference(self::getReferenceName(), $companyWithOwner);
    }

    private function createCompany(): Company
    {
        $name = self::getReferenceName();

        $region = $this->getReference(LoadNovosibirskRegion::REFERENCE_NAME);
        assert($region instanceof Region);

        $location = new Location(Uuid::uuid4(), new Coordinates(55.1, 83));
        $location->setRegion($region);

        $contactDTO = $this->contactDTOFakeFactory->createFakeContactDTO();
        $contactDTO->locations = new LocationCollection([$location]);

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
            LoadNovosibirskRegion::class,
            LoadTestUser::class,
            LoadDefaultRubric::class,
        ];
    }
}
