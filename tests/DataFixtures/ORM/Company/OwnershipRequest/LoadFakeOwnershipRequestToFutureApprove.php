<?php

namespace Tests\DataFixtures\ORM\Company\OwnershipRequest;

use App\Domain\Company\Entity\Company;
use App\Domain\Company\Entity\OwnershipRequest;
use App\Domain\User\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Ramsey\Uuid\Uuid;
use Tests\DataFixtures\ORM\Company\Company\LoadCompanyWithoutOwnerToFutureApproveOwnershipRequest;
use Tests\DataFixtures\ORM\User\LoadTestUser;

class LoadFakeOwnershipRequestToFutureApprove extends Fixture implements DependentFixtureInterface, FixtureInterface
{
    public const REFERENCE_NAME = 'fake-ownership-request-to-future-approve';

    public function load(ObjectManager $manager): void
    {
        /** @var User $creator */
        $creator = $this->getReference(LoadTestUser::USER_TEST);

        /** @var Company $company */
        $company = $this->getReference(LoadCompanyWithoutOwnerToFutureApproveOwnershipRequest::REFERENCE_NAME);

        $ownershipRequest = new OwnershipRequest(Uuid::uuid4(), $creator, $company);
        $this->addReference(self::REFERENCE_NAME, $ownershipRequest);

        $manager->persist($ownershipRequest);
        $manager->flush();
    }

    /**
     * @return string[]
     */
    public function getDependencies(): array
    {
        return [
            LoadTestUser::class,
            LoadCompanyWithoutOwnerToFutureApproveOwnershipRequest::class,
        ];
    }
}
