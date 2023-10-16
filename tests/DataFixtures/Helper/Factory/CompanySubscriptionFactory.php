<?php

namespace Tests\DataFixtures\Helper\Factory;

use App\Domain\BusinessSubscription\Entity\CompanySubscription;
use App\Domain\BusinessSubscription\Entity\ValueObject\TariffsType;
use Carbon\CarbonImmutable;
use DateInterval;
use Ramsey\Uuid\Uuid;

class CompanySubscriptionFactory
{
    public function createMonthlySubscription(): CompanySubscription
    {
        $id = Uuid::uuid4();

        return new CompanySubscription(
            $id,
            TariffsType::standard(),
            CarbonImmutable::today(),
            CarbonImmutable::today()->addDays(30)
        );
    }

    public function createMonthlyPremiumSubscription(): CompanySubscription
    {
        $id = Uuid::uuid4();

        return new CompanySubscription(
            $id,
            TariffsType::premium(),
            CarbonImmutable::today(),
            CarbonImmutable::today()->addDays(30)
        );
    }

    public function createExpiredSubscription(): CompanySubscription
    {
        $id = Uuid::uuid4();

        return new CompanySubscription(
            $id,
            TariffsType::standard(),
            CarbonImmutable::yesterday()->subDays(10),
            CarbonImmutable::yesterday()
        );
    }

    public function createRenewalSubscription(CompanySubscription $previousCompanySubscription): CompanySubscription
    {
        $id = Uuid::uuid4();

        return new CompanySubscription(
            $id,
            TariffsType::standard(),
            $previousCompanySubscription->getExpiredAt()->add(new DateInterval('P1D')),
            $previousCompanySubscription->getExpiredAt()->add(new DateInterval('P30D'))
        );
    }

    public function createFutureSubscription(): CompanySubscription
    {
        $id = Uuid::uuid4();

        return new CompanySubscription(
            $id,
            TariffsType::standard(),
            CarbonImmutable::tomorrow()->addDays(30),
            CarbonImmutable::tomorrow()->addDays(60)
        );
    }
}
