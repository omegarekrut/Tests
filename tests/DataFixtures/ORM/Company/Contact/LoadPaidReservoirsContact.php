<?php

namespace Tests\DataFixtures\ORM\Company\Contact;

use App\Domain\Company\Entity\Company;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Tests\DataFixtures\Helper\Factory\ContactDTOFakeFactory;
use Tests\DataFixtures\ORM\Company\Company\LoadPaidReservoirsCompany;

class LoadPaidReservoirsContact extends Fixture implements DependentFixtureInterface
{
    public const REFERENCE_NAME = 'paid-reservoirs-contact';

    private ContactDTOFakeFactory $contactDTOFakeFactory;

    public function __construct(ContactDTOFakeFactory $contactDTOFakeFactory)
    {
        $this->contactDTOFakeFactory = $contactDTOFakeFactory;
    }

    public function load(ObjectManager $manager): void
    {
        /** @var Company $company */
        $company = $this->getReference(LoadPaidReservoirsCompany::REFERENCE_NAME);

        $company->rewriteContactsFromDTO(
            $this->contactDTOFakeFactory->createFakeContactDTOWithoutAddress()
        );

        $this->addReference(self::REFERENCE_NAME, $company->getContact());

        $manager->flush();
    }

    /**
     * @return string[]
     */
    public function getDependencies(): array
    {
        return [
            LoadPaidReservoirsCompany::class,
        ];
    }
}
