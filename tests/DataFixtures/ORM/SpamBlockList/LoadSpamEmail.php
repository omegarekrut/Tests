<?php

namespace Tests\DataFixtures\ORM\SpamBlockList;

use App\Domain\SpamBlockList\Entity\SpamEmail;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class LoadSpamEmail extends Fixture
{
    public const REFERENCE_NAME = 'spam-email';

    public function load(ObjectManager $manager): void
    {
        $spamEmail = new SpamEmail('spam-email@gmail.com');

        $manager->persist($spamEmail);

        $this->addReference(self::REFERENCE_NAME, $spamEmail);

        $manager->flush();
    }
}
