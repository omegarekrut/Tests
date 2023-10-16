<?php

namespace Tests\DataFixtures\ORM\Ban;

use App\Domain\Ban\Entity\BanUser;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Tests\DataFixtures\ORM\User\LoadBannedUser;
use Tests\DataFixtures\ORM\User\LoadModeratorUser;
use Tests\DataFixtures\ORM\User\LoadSpammerUser;

class LoadBanUsers extends Fixture implements DependentFixtureInterface
{
    public const BAN_USER = 'ban-user';
    public const BAN_USER_SPAMMER = 'ban-spammer';

    public function load(ObjectManager $manager): void
    {
        $banUser = new BanUser(
            $this->getReference(LoadBannedUser::USER_BANNED),
            $this->getReference(LoadModeratorUser::REFERENCE_NAME),
            'test'
        );

        $manager->persist($banUser);

        $banForSpammer = new BanUser(
            $this->getReference(LoadSpammerUser::REFERENCE_NAME),
            $this->getReference(LoadModeratorUser::REFERENCE_NAME),
            'Ручная очистка спама'
        );

        $manager->persist($banForSpammer);

        $manager->flush();

        $this->addReference(self::BAN_USER, $banUser);
        $this->addReference(self::BAN_USER_SPAMMER, $banForSpammer);
    }

    /**
     * @return string[]
     */
    public function getDependencies(): array
    {
        return [
            LoadModeratorUser::class,
            LoadSpammerUser::class,
            LoadBannedUser::class,
        ];
    }
}
