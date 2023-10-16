<?php

namespace Tests\DataFixtures\ORM\Record\Tidings;

use Carbon\Carbon;
use Doctrine\Common\Persistence\ObjectManager;

class LoadOldTidings extends LoadNumberedTidings
{
    protected const REFERENCE_PREFIX = 'old-tidings';
    protected const REFERENCE_PREFIX_WITH_HASHTAG = 'old-tidings-with-hashtag';
    protected const COUNT = 10;

    public function load(ObjectManager $manager): void
    {
        Carbon::setTestNow(Carbon::now()->subYears(2));

        parent::load($manager);

        Carbon::setTestNow();
    }
}
