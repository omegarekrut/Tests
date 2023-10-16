<?php

namespace Tests\Functional\Domain\BusinessSubscription\Command\Handler;

use App\Domain\BusinessSubscription\Command\CreateCompanySubscriptionCommand;
use App\Domain\BusinessSubscription\Entity\ValueObject\TariffsType;
use App\Domain\Company\Entity\Company;
use DateTime;
use Ramsey\Uuid\Uuid;
use Tests\DataFixtures\ORM\Company\Company\LoadCompanyWithoutOwner;
use Tests\Functional\TestCase;

/**
 * @group business_subscription
 */
final class CreateCompanySubscriptionHandlerTest extends TestCase
{
    public function testCreateCompanySubscription(): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadCompanyWithoutOwner::class,
        ])->getReferenceRepository();

        $company = $referenceRepository->getReference(LoadCompanyWithoutOwner::REFERENCE_NAME);
        assert($company instanceof Company);

        $command = new CreateCompanySubscriptionCommand(Uuid::uuid4(), $company);

        $command->tariff = TariffsType::standard();
        $command->comment = 'New comment';
        $command->externalPaymentId = 'New external payment #123';
        $command->startedAt = (new DateTime())->modify('-1 day');
        $command->expiredAt = new DateTime();

        $this->getCommandBus()->handle($command);

        $updatingCompanySubscription = $company->getSubscriptions()->first();

        $this->assertEquals($command->tariff, $updatingCompanySubscription->getTariff());
        $this->assertEquals($command->externalPaymentId, $updatingCompanySubscription->getExternalPaymentId());
        $this->assertEquals($command->comment, $updatingCompanySubscription->getComment());
        $this->assertEquals($command->startedAt, $updatingCompanySubscription->getStartedAt());
        $this->assertEquals($command->expiredAt, $updatingCompanySubscription->getExpiredAt());
    }
}
