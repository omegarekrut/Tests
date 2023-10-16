<?php

namespace Tests\DataFixtures\ORM;

use App\Domain\Advertising\Entity\StatisticItem;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class LoadStatistics extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $repository = $manager->getRepository(StatisticItem::class);

        $repository->saveMonthlyVisitors(100);
        $repository->saveVkSubscribers(200);
        $repository->saveEmailSubscribers(300);
        $repository->saveYoutubeSubscribers(400);
        $repository->saveCompaniesHasOwner(1000);
    }
}
