<?php

namespace Tests\Functional\Domain\User\Command\Subscription;

use App\Domain\Company\Entity\Company;
use App\Domain\User\Command\Subscription\UnsubscribeFromCompanyCommand;
use App\Domain\User\Entity\User;
use Tests\DataFixtures\ORM\Company\Company\LoadCompanyWithOwner;
use Tests\DataFixtures\ORM\Company\Company\LoadCompanyWithSubscriber;
use Tests\DataFixtures\ORM\User\LoadAdminUser;
use Tests\DataFixtures\ORM\User\LoadUserWithSubscriptionOnCompany;
use Tests\Functional\ValidationTestCase;

/**
 * @group user-subscription
 */
class UnsubscribeFromCompanyCommandValidationTest extends ValidationTestCase
{
    private User $subscriber;
    private User $user;
    private Company $company;

    protected function setUp(): void
    {
        parent::setUp();

        $referenceRepository = $this->loadFixtures([
            LoadUserWithSubscriptionOnCompany::class,
            LoadCompanyWithOwner::class,
            LoadAdminUser::class,
        ])->getReferenceRepository();

        $this->company = $referenceRepository->getReference(LoadCompanyWithSubscriber::REFERENCE_NAME);
        $this->subscriber = $referenceRepository->getReference(LoadUserWithSubscriptionOnCompany::REFERENCE_NAME);
        $this->user = $referenceRepository->getReference(LoadAdminUser::REFERENCE_NAME);
    }

    protected function tearDown(): void
    {
        unset($this->subscriber, $this->company, $this->user);

        parent::tearDown();
    }

    public function testUserHasNotSubscriptionOnCompany(): void
    {
        $command = new UnsubscribeFromCompanyCommand($this->user, $this->company);

        $this->getValidator()->validate($command);

        $this->assertGreaterThan(0, count($this->getValidator()->getLastErrors()));
    }

    public function testUserHasSubscriptionOnCompany(): void
    {
        $command = new UnsubscribeFromCompanyCommand($this->subscriber, $this->company);

        $this->getValidator()->validate($command);

        $this->assertEmpty($this->getValidator()->getLastErrors());
    }
}
