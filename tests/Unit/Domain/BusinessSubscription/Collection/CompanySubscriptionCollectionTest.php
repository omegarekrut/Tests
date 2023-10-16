<?php

namespace Tests\Unit\Domain\BusinessSubscription\Collection;

use App\Domain\BusinessSubscription\Collection\CompanySubscriptionCollection;
use Doctrine\Common\Collections\ArrayCollection;
use Generator;
use Tests\DataFixtures\Helper\Factory\CompanySubscriptionFactory;
use Tests\Unit\TestCase;

class CompanySubscriptionCollectionTest extends TestCase
{
    /**
     * @dataProvider getHasActiveSubscriptionCollections
     */
    public function testHasActiveSubscription(ArrayCollection $collection, bool $expectedResult): void
    {
        $companySubscriptionCollection = new CompanySubscriptionCollection($collection);

        $this->assertEquals($expectedResult, $companySubscriptionCollection->hasActiveSubscription());
    }

    public function getHasActiveSubscriptionCollections(): Generator
    {
        $companySubscriptionFactory = new CompanySubscriptionFactory();

        yield [new ArrayCollection(), false];

        $activeSubscription = $companySubscriptionFactory->createMonthlySubscription();

        yield 'active subscription' => [
            new ArrayCollection([
                $activeSubscription,
            ]),
            true,
        ];

        yield 'expired subscription' => [
            new ArrayCollection([
                $companySubscriptionFactory->createExpiredSubscription(),
            ]),
            false,
        ];

        yield 'future subscription' => [
            new ArrayCollection([
                $companySubscriptionFactory->createFutureSubscription(),
            ]),
            false,
        ];

        yield 'collection with active subscription' => [
            new ArrayCollection([
                $activeSubscription,
                $companySubscriptionFactory->createRenewalSubscription($activeSubscription),
            ]),
            true,
        ];
    }
}
