<?php

namespace Tests\DataFixtures\Helper\Factory;

use App\Domain\Company\Collection\LocationCollection;
use App\Domain\Company\Collection\UrlAddressCollection;
use App\Domain\Company\Entity\Location;
use App\Domain\Company\Entity\Phone;
use App\Domain\Company\Entity\ValueObject\ContactDTO;
use App\Domain\Company\Entity\ValueObject\UrlAddress;
use App\Util\Coordinates\Coordinates;
use Doctrine\Common\Collections\ArrayCollection;
use Faker\Generator;
use Ramsey\Uuid\Uuid;

class ContactDTOFakeFactory
{
    private Generator $faker;

    public function __construct(Generator $faker)
    {
        $this->faker = $faker;
    }

    public function createFakeContactDTO(): ContactDTO
    {
        $contactDTO = $this->createFake();
        $contactDTO->locations = new LocationCollection([$this->createLocationWithAddress()]);

        return $contactDTO;
    }

    public function createFakeContactDTOWithoutAddress(): ContactDTO
    {
        $contactDTO = $this->createFake();
        $contactDTO->locations = new LocationCollection([$this->createLocationWithoutAddress()]);

        return $contactDTO;
    }

    public function createFakeContactDTOWithFixedCoordinates(): ContactDTO
    {
        $contactDTO = $this->createFake();
        $contactDTO->locations = new LocationCollection([$this->createLocationWithFixedCoordinates()]);

        return $contactDTO;
    }

    private function createFake(): ContactDTO
    {
        $contactDTO = new ContactDTO();

        $contactDTO->email = $this->faker->companyEmail;
        $contactDTO->whatsapp = '+7 (999) 606-69-42';
        $contactDTO->sites = $this->createSites();
        $contactDTO->phones = $this->createPhones();
        $contactDTO->isDeliveryToRegionsAvailable = false;

        return $contactDTO;
    }

    private function createSites(): UrlAddressCollection
    {
        return new UrlAddressCollection([
            new UrlAddress($this->faker->url),
            new UrlAddress($this->faker->url),
        ]);
    }

    private function createPhones(): ArrayCollection
    {
        return new ArrayCollection([
            new Phone(Uuid::uuid4(), '+7 (888) 606-69-42', $this->faker->streetName),
            new Phone(Uuid::uuid4(), '+7 (777) 606-69-42', $this->faker->streetName),
        ]);
    }

    private function createLocationWithoutAddress(): Location
    {
        $coordinates = new Coordinates(
            $this->faker->randomFloat(6, 54, 55),
            $this->faker->randomFloat(6, 82, 84)
        );

        $location = new Location(Uuid::uuid4(), $coordinates);
        $location->setSchedule('Ежедневно 09:00 - 18:00');
        $location->setHowToFind($this->faker->sentence);

        return $location;
    }

    private function createLocationWithAddress(): Location
    {
        $location = $this->createLocationWithoutAddress();
        $location->setAddress($this->faker->address);

        return $location;
    }

    private function createLocationWithFixedCoordinates(): Location
    {
        $coordinates = new Coordinates(
            55.0520939,
            82.874782
        );

        $location = new Location(Uuid::uuid4(), $coordinates);
        $location->setSchedule('Ежедневно 09:00 - 18:00');
        $location->setHowToFind($this->faker->sentence);
        $location->setAddress($this->faker->address);

        return $location;
    }
}
