<?php

namespace Tests\Functional\Domain\User\Collection;

use App\Domain\User\Collection\UserCollection;
use App\Domain\User\Entity\User;
use Doctrine\Common\DataFixtures\ReferenceRepository;
use Tests\DataFixtures\ORM\User\LoadModeratorAdvancedUser;
use Tests\DataFixtures\ORM\User\LoadModeratorUser;
use Tests\DataFixtures\ORM\User\LoadTestUser;
use Tests\Functional\TestCase;

class UserCollectionTest extends TestCase
{
    /** @var ReferenceRepository */
    private $referenceRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->referenceRepository = $this->loadFixtures([
            LoadTestUser::class,
            LoadModeratorAdvancedUser::class,
            LoadModeratorUser::class,
        ])->getReferenceRepository();
    }

    protected function tearDown(): void
    {
        unset(
            $this->referenceRepository,
            $this->user,
            $this->userRepository
        );

        parent::tearDown();
    }

    public function testGetCollectionWithoutAuthor(): void
    {
        /** @var User $author */
        $author = $this->referenceRepository->getReference(LoadTestUser::USER_TEST);

        $userCollection = new UserCollection([
            $this->referenceRepository->getReference(LoadTestUser::USER_TEST),
            $this->referenceRepository->getReference(LoadModeratorAdvancedUser::REFERENCE_NAME),
            $this->referenceRepository->getReference(LoadModeratorUser::REFERENCE_NAME),
        ]);

        $userCollectionWithoutAuthor = $userCollection->withoutAuthor($author);

        $this->assertFalse($userCollectionWithoutAuthor->contains($author));
    }

    public function testGetCollectionOrderingByUsernameLengthDesc(): void
    {
        $userCollection = new UserCollection([
            $this->referenceRepository->getReference(LoadTestUser::USER_TEST),
            $this->referenceRepository->getReference(LoadModeratorAdvancedUser::REFERENCE_NAME),
            $this->referenceRepository->getReference(LoadModeratorUser::REFERENCE_NAME),
        ]);

        $userCollectionOrderingByUsernameLength = $userCollection->orderByUsernameLengthDesc();

        $this->assertEquals([
            $this->referenceRepository->getReference(LoadModeratorAdvancedUser::REFERENCE_NAME),
            $this->referenceRepository->getReference(LoadModeratorUser::REFERENCE_NAME),
            $this->referenceRepository->getReference(LoadTestUser::USER_TEST),
        ], $userCollectionOrderingByUsernameLength->toArray());
    }

    public function getMergedCollection(): void
    {
        $firstUserCollection = new UserCollection([
            $this->referenceRepository->getReference(LoadTestUser::USER_TEST),
            $this->referenceRepository->getReference(LoadModeratorUser::REFERENCE_NAME),
            $this->referenceRepository->getReference(LoadModeratorAdvancedUser::REFERENCE_NAME),
        ]);

        $secondUserCollection = new UserCollection([
            $this->referenceRepository->getReference(LoadModeratorUser::REFERENCE_NAME),
            $this->referenceRepository->getReference(LoadTestUser::USER_TEST),
        ]);

        $mergedUserCollection = $firstUserCollection->merge($secondUserCollection);

        $this->assertEquals(5, $mergedUserCollection->count());
    }

    public function getUniqueCollection(): void
    {
        $firstUserCollection = new UserCollection([
            $this->referenceRepository->getReference(LoadTestUser::USER_TEST),
            $this->referenceRepository->getReference(LoadModeratorAdvancedUser::REFERENCE_NAME),
            $this->referenceRepository->getReference(LoadModeratorUser::REFERENCE_NAME),
            $this->referenceRepository->getReference(LoadModeratorUser::REFERENCE_NAME),
            $this->referenceRepository->getReference(LoadTestUser::USER_TEST),
        ]);

        $mergedUserCollection = $firstUserCollection->unique();

        $this->assertEquals(3, $mergedUserCollection->count());
    }
}
