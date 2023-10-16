<?php

namespace Tests\Functional\Domain\Advertising;

use App\Domain\Advertising\Entity\StatisticItem;
use App\Domain\Advertising\Repository\StatisticRepository;
use Tests\DataFixtures\ORM\LoadStatistics;
use Tests\Functional\RepositoryTestCase;

class AdvertisingRepositoryTest extends RepositoryTestCase
{
    /* @var StatisticRepository $repository */
    private $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->loadFixtures([
            LoadStatistics::class,
        ]);

        $this->repository = $this->getRepository(StatisticItem::class);
    }

    protected function tearDown(): void
    {
        unset($this->repository);

        parent::tearDown();
    }

    public function testStatistic(): void
    {
        $this->assertEquals(300, $this->repository->getEmailSubscribers());
        $this->assertEquals(400, $this->repository->getYoutubeSubscribers());
        $this->assertEquals(200, $this->repository->getVkSubscribers());
        $this->assertEquals(100, $this->repository->getMonthlyVisitors());
    }
}
