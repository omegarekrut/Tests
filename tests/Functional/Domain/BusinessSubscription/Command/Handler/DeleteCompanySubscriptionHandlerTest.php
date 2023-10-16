<?php

namespace Tests\Functional\Domain\BusinessSubscription\Command\Handler;

use App\Domain\BusinessSubscription\Command\DeleteCompanySubscriptionCommand;
use App\Domain\BusinessSubscription\Entity\CompanySubscription;
use App\Domain\Company\Entity\Company;
use Tests\DataFixtures\ORM\Company\CompanyWithSubscription\LoadCompanyWithActiveSubscription;
use Tests\Functional\TestCase;

/**
 * @group business_subscription
 */
final class DeleteCompanySubscriptionHandlerTest extends TestCase
{
    public function testDeleteCompanySubscription(): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadCompanyWithActiveSubscription::class,
        ])->getReferenceRepository();

        $companyWithActiveSubscription = $referenceRepository->getReference(LoadCompanyWithActiveSubscription::REFERENCE_NAME);
        assert($companyWithActiveSubscription instanceof Company);

        $deletingCompanySubscription = $companyWithActiveSubscription->getSubscriptions()->first();

        $command = new DeleteCompanySubscriptionCommand($deletingCompanySubscription);
        $this->getCommandBus()->handle($command);

        $companySubscriptionRepository = $this->getEntityManager()->getRepository(CompanySubscription::class);
        $deletedCompanySubscription = $companySubscriptionRepository->find($deletingCompanySubscription->getId());

        $this->assertEmpty($deletedCompanySubscription);
        $this->assertFalse($companyWithActiveSubscription->getSubscriptions()->hasActiveSubscription());
    }
}
