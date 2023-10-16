<?php

namespace Tests\DataFixtures\ORM\Company\Contact;

use App\Domain\Company\Entity\Company;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Tests\DataFixtures\Helper\Factory\ContactDTOFakeFactory;
use Tests\DataFixtures\ORM\Company\Company\LoadCompanyWithSocialLinks;

class LoadCompanyWithSocialLinksContact extends Fixture implements DependentFixtureInterface
{
    public const REFERENCE_NAME = 'company-with-social-links-contact';

    private ContactDTOFakeFactory $contactDTOFakeFactory;

    public function __construct(ContactDTOFakeFactory $contactDTOFakeFactory)
    {
        $this->contactDTOFakeFactory = $contactDTOFakeFactory;
    }

    public function load(ObjectManager $manager): void
    {
        /** @var Company $company */
        $company = $this->getReference(LoadCompanyWithSocialLinks::REFERENCE_NAME);

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
            LoadCompanyWithSocialLinks::class,
        ];
    }
}
