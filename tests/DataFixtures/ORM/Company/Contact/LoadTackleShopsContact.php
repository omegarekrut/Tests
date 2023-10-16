<?php

namespace Tests\DataFixtures\ORM\Company\Contact;

use App\Domain\Company\Entity\Company;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Tests\DataFixtures\Helper\Factory\ContactDTOFakeFactory;
use Tests\DataFixtures\ORM\Company\Company\LoadTackleShopsCompany;

class LoadTackleShopsContact extends Fixture implements DependentFixtureInterface
{
    public const REFERENCE_NAME = 'tackle-shops-contact';

    private ContactDTOFakeFactory $contactDTOFakeFactory;

    public function __construct(ContactDTOFakeFactory $contactDTOFakeFactory)
    {
        $this->contactDTOFakeFactory = $contactDTOFakeFactory;
    }

    public function load(ObjectManager $manager): void
    {
        /** @var Company $company */
        $company = $this->getReference(LoadTackleShopsCompany::REFERENCE_NAME);

        $company->rewriteContactsFromDTO(
            $this->contactDTOFakeFactory->createFakeContactDTO()
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
            LoadTackleShopsCompany::class,
        ];
    }
}
