<?php

namespace Tests\DataFixtures\ORM\User;

use App\Domain\Ban\Entity\BanUser;
use App\Domain\Log\Entity\SuspiciousLoginLog;
use App\Domain\User\Entity\User;
use App\Domain\User\Entity\ValueObject\LastVisit;
use App\Domain\User\Entity\ValueObject\PasswordHashingOptions;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Faker\Generator;
use Tests\DataFixtures\Helper\DefaultUserPasswordGenerator;
use Tests\DataFixtures\ORM\Ban\LoadBanUsers;

final class LoadUserWithSuspiciousLoginFromBannedAccount extends UserFixture implements DependentFixtureInterface
{
    public const REFERENCE_NAME = 'user-with-suspicious-login-from-banned-account';

    private \Faker\Generator $generator;
    private DefaultUserPasswordGenerator $passwordGenerator;

    public function __construct(\Faker\Generator $generator, DefaultUserPasswordGenerator $passwordGenerator)
    {
        $this->generator = $generator;
        $this->passwordGenerator = $passwordGenerator;
    }

    public function load(ObjectManager $manager): void
    {
        $lastVisit = new LastVisit($this->generator->ipv4, new \DateTime());

        $user = new User(
            'user-with-suspicious-login-from-banned-account',
            'user-with-suspicious-login-from-banned-account@gmail.com',
            $this->passwordGenerator->generate(),
            new PasswordHashingOptions(),
            $lastVisit
        );

        $user
            ->confirmEmail()
            ->setForumUserId(self::getForumUserId());

        $manager->persist($user);
        $this->addReference(self::REFERENCE_NAME, $user);

        $userBan = $this->getReference(LoadBanUsers::BAN_USER);
        assert($userBan instanceof BanUser);

        $suspiciousLoginLog = new SuspiciousLoginLog(
            $userBan->getUser(),
            $user
        );
        $manager->persist($suspiciousLoginLog);

        $manager->flush();
    }

    /**
     * @return string[]
     */
    public function getDependencies(): array
    {
        return [
            LoadBanUsers::class,
        ];
    }
}
