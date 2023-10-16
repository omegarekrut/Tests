<?php

namespace Tests\DataFixtures\ORM\User;

use Carbon\Carbon;
use Doctrine\Common\Persistence\ObjectManager;

class LoadOldUsers extends LoadNumberedUsers
{
    protected const REFERENCE_PREFIX = 'user-old';

    public function load(ObjectManager $manager): void
    {
        Carbon::setTestNow(Carbon::now()->subYears(2));

        parent::load($manager);

        Carbon::setTestNow();
    }
}
