<?php

namespace Tests\DataFixtures\ORM\Company\OwnershipRequest;

use App\Domain\Company\Entity\Company;
use App\Domain\Company\Entity\OwnershipRequest;
use App\Domain\User\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Ramsey\Uuid\Uuid;
use Tests\DataFixtures\ORM\Company\Company\LoadCompanyWithoutOwnerToFutureApproveOwnershipRequest;
use Tests\DataFixtures\ORM\SingleReferenceFixtureInterface;
use Tests\DataFixtures\ORM\User\LoadNumberedUsers;

class LoadOwnershipRequestToFutureApprove extends Fixture implements SingleReferenceFixtureInterface, DependentFixtureInterface
{
    /** @deprecated Use {@link getReferenceName} */
    public const REFERENCE_NAME = 'ownership-request-to-future-approve';

    public static function getReferenceName(): string
    {
        return self::REFERENCE_NAME;
    }

    public function load(ObjectManager $manager): void
    {
        /** @var User $creator */
        $creator = $this->getReference(LoadNumberedUsers::getRandReferenceName());

        /** @var Company $company */
        $company = $this->getReference(LoadCompanyWithoutOwnerToFutureApproveOwnershipRequest::REFERENCE_NAME);

        $ownershipRequest = new OwnershipRequest(Uuid::uuid4(), $creator, $company);
        $this->addReference(static::getReferenceName(), $ownershipRequest);

        $manager->persist($ownershipRequest);
        $manager->flush();
    }

    /**
     * @return string[]
     */
    public function getDependencies(): array
    {
        return [
            LoadNumberedUsers::class,
            LoadCompanyWithoutOwnerToFutureApproveOwnershipRequest::class,
        ];
    }
}
