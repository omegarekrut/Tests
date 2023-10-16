<?php

namespace Tests\DataFixtures\ORM\User;

use App\Domain\User\Entity\User;
use App\Domain\User\Entity\ValueObject\LastVisit;
use App\Domain\User\Entity\ValueObject\PasswordHashingOptions;
use Doctrine\Common\Persistence\ObjectManager;
use Tests\DataFixtures\Helper\DefaultUserPasswordGenerator;

/**
 * @todo not the correct name for fixture. Refactoring required
 */
class LoadBannedUser extends UserFixture
{
    public const USER_BANNED = 'user-banned';

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

        $userBanned = new User(
            'user-banned',
            'user-banned@gmail.com',
            $this->passwordGenerator->generate(),
            new PasswordHashingOptions(),
            $lastVisit
        );

        $userBanned
            ->confirmEmail()
            ->updateGlobalRating(20)
            ->setForumUserId(self::getForumUserId());

        $manager->persist($userBanned);
        $this->addReference(self::USER_BANNED, $userBanned);

        $manager->flush();
    }
}
