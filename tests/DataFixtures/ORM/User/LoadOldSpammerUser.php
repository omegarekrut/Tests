<?php

namespace Tests\DataFixtures\ORM\User;

use Carbon\Carbon;
use Doctrine\Common\Persistence\ObjectManager;

class LoadOldSpammerUser extends LoadSpammerUser
{
    public const REFERENCE_NAME = 'user-old-spammer';

    public function load(ObjectManager $manager): void
    {
        Carbon::setTestNow(Carbon::now()->subYears(2));

        parent::load($manager);

        Carbon::setTestNow();
    }
}
