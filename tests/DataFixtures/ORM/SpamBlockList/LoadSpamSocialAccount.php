<?php

namespace Tests\DataFixtures\ORM\SpamBlockList;

use App\Domain\SpamBlockList\Entity\SpamSocialAccount;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class LoadSpamSocialAccount extends Fixture
{
    public const REFERENCE_NAME = 'spam-social-account';

    public function load(ObjectManager $manager): void
    {
        $spamSocialAccount = new SpamSocialAccount('vkontakte', '4815162342');

        $manager->persist($spamSocialAccount);

        $this->addReference(self::REFERENCE_NAME, $spamSocialAccount);

        $manager->flush();
    }
}
