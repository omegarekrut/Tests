<?php

namespace Tests\DataFixtures\Helper\Factory;

use App\Domain\Company\Collection\LocationCollection;
use App\Domain\Company\Entity\Company;
use App\Domain\Company\Entity\Location;
use App\Domain\Company\Entity\ValueObject\ContactDTO;
use App\Module\ShortUuid\ShortUuidConverterInterface;
use App\Module\SlugGenerator\SlugGenerator;
use App\Util\Coordinates\Coordinates;
use Doctrine\Common\Collections\ArrayCollection;
use Faker\Generator;
use Ramsey\Uuid\Uuid;

class CompanyFactory
{
    private Generator $faker;
    private SlugGenerator $slugGenerator;
    private ShortUuidConverterInterface $shortUuidConverter;
    private ContactDTOFakeFactory $contactDTOFakeFactory;

    public function __construct(
        Generator $faker,
        SlugGenerator $slugGenerator,
        ShortUuidConverterInterface $shortUuidConverter,
        ContactDTOFakeFactory $contactDTOFakeFactory
    ) {
        $this->faker = $faker;
        $this->slugGenerator = $slugGenerator;
        $this->shortUuidConverter = $shortUuidConverter;
        $this->contactDTOFakeFactory = $contactDTOFakeFactory;
    }

    public function createCompanyWithCoordinatesLocation(
        string $name,
        ArrayCollection $rubrics,
        Coordinates $coordinates
    ): Company {
        $company = $this->createCompany($name, $rubrics);

        $company->rewriteContactsFromDTO(
            $this->createContactDTO($coordinates)
        );

        return $company;
    }

    public function createCompany(string $name, ArrayCollection $rubrics): Company
    {
        $id = Uuid::uuid4();

        return new Company(
            $id,
            $name,
            $this->slugGenerator->generate($name, Company::class),
            $this->shortUuidConverter->encode($id),
            $this->faker->catchPhrase,
            $rubrics
        );
    }

    private function createContactDTO(Coordinates $coordinates): ContactDTO
    {
        $contactDTO = $this->contactDTOFakeFactory->createFakeContactDTO();
        $contactDTO->locations = new LocationCollection([new Location(Uuid::uuid4(), clone $coordinates)]);

        return $contactDTO;
    }
}
