<?php

namespace Tests\Functional\Domain\BusinessSubscription\Command\Handler;

use App\Domain\BusinessSubscription\Command\UpdateCompanySubscriptionCommand;
use App\Domain\Company\Entity\Company;
use DateTime;
use Tests\DataFixtures\ORM\Company\CompanyWithSubscription\LoadCompanyWithActiveSubscription;
use Tests\Functional\TestCase;

/**
 * @group business_subscription
 */
final class UpdateCompanySubscriptionHandlerTest extends TestCase
{
    public function testDeleteCompanySubscription(): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadCompanyWithActiveSubscription::class,
        ])->getReferenceRepository();

        $companyWithActiveSubscription = $referenceRepository->getReference(LoadCompanyWithActiveSubscription::REFERENCE_NAME);
        assert($companyWithActiveSubscription instanceof Company);

        $updatingCompanySubscription = $companyWithActiveSubscription->getSubscriptions()->first();

        $command = new UpdateCompanySubscriptionCommand($updatingCompanySubscription);

        $command->comment = 'Updated comment';
        $command->externalPaymentId = 'New external payment #123';
        $command->expiredAt = new DateTime();

        $this->getCommandBus()->handle($command);

        $this->assertEquals($command->externalPaymentId, $updatingCompanySubscription->getExternalPaymentId());
        $this->assertEquals($command->comment, $updatingCompanySubscription->getComment());
        $this->assertEquals($command->expiredAt, $updatingCompanySubscription->getExpiredAt());
    }
}
