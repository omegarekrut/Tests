<?php

namespace Tests\DataFixtures\ORM\Ban;

use App\Domain\Ban\Entity\BanIp;
use App\Domain\Ban\Factory\IpRangeFactory;
use Carbon\Carbon;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Tests\DataFixtures\ORM\User\LoadAdminUser;

class LoadBanIp extends Fixture implements DependentFixtureInterface
{
    public const BAN_IP = 'ban-ip';
    public const BAN_IP_SPAMMER = 'ban-ip-spammer';
    public const BAN_IP_EXPIRED = 'ban-ip-expired';

    private IpRangeFactory $ipRangeFactory;

    public function __construct(IpRangeFactory $ipRangeFactory)
    {
        $this->ipRangeFactory = $ipRangeFactory;
    }

    public function load(ObjectManager $manager): void
    {
        $this->loadSimpleBan($manager);
        $this->loadSpammerBan($manager);
        $this->loadExpiredBan($manager);

        $manager->flush();
    }

    private function loadSimpleBan(ObjectManager $manager): void
    {
        $banIp = new BanIp(
            $this->ipRangeFactory->createFromString('8.8.8.8/24'),
            $this->getReference(LoadAdminUser::REFERENCE_NAME),
            'test'
        );
        $manager->persist($banIp);
        $this->addReference(static::BAN_IP, $banIp);
    }

    private function loadSpammerBan(ObjectManager $manager): void
    {
        $banIp = new BanIp(
            $this->ipRangeFactory->createFromString('1.1.1.1/24'),
            $this->getReference(LoadAdminUser::REFERENCE_NAME),
            'Ручная очистка спама'
        );

        $manager->persist($banIp);
        $this->addReference(static::BAN_IP_SPAMMER, $banIp);
    }

    private function loadExpiredBan(ObjectManager $manager): void
    {
        $banIp = new BanIp(
            $this->ipRangeFactory->createFromString('2001:db8:11a3:9d7:1f34:8a2e:7a0:765d'),
            $this->getReference(LoadAdminUser::REFERENCE_NAME),
            'test',
            Carbon::now()->subDay()
        );

        $manager->persist($banIp);
        $this->addReference(static::BAN_IP_EXPIRED, $banIp);
    }

    /**
     * @return string[]
     */
    public function getDependencies(): array
    {
        return [
            LoadAdminUser::class,
        ];
    }
}
