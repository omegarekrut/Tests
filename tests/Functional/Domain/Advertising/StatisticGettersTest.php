<?php

namespace Tests\Functional\Domain\Advertising;

use App\Domain\Advertising\Exception\InvalidConfigurationAdvertisingStatisticGetter;
use App\Domain\Advertising\StatisticGetter\CompaniesHasOwnerGetter;
use App\Domain\Advertising\StatisticGetter\EmailSubscribersGetter;
use App\Domain\Advertising\StatisticGetter\MonthlyVisitorsGetter;
use App\Domain\Advertising\StatisticGetter\VkSubscribersGetter;
use App\Domain\Advertising\StatisticGetter\YoutubeSubscribersGetter;
use Generator;
use Tests\DataFixtures\ORM\Company\Company\LoadAquaMotorcycleShopsCompany;
use Tests\DataFixtures\ORM\User\LoadModeratorAdvancedUser;
use Tests\Functional\TestCase;

/**
 * @group advertising
 */
class StatisticGettersTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->loadFixtures([
            LoadModeratorAdvancedUser::class,
            LoadAquaMotorcycleShopsCompany::class,
        ]);
    }

    /**
     * @dataProvider getGetters
     */
    public function testGetterResult(string $getterClass): void
    {
        $getter = $this->getContainer()->get($getterClass);

        try {
            $result = $getter->getStatisticValue();
        } catch (InvalidConfigurationAdvertisingStatisticGetter $exception) {
            $this->markTestSkipped($exception->getMessage());
        }

        $this->assertIsInt($result);
        $this->assertGreaterThan(0, $result);
    }

    public function getGetters(): Generator
    {
        yield 'youtube' => [
            YoutubeSubscribersGetter::class,
        ];

        yield 'vk' => [
            VkSubscribersGetter::class,
        ];

        yield 'email' => [
            EmailSubscribersGetter::class,
        ];

        yield 'visitors' => [
            MonthlyVisitorsGetter::class,
        ];

        yield 'companies' => [
            CompaniesHasOwnerGetter::class,
        ];
    }
}
