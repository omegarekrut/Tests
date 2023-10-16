<?php

namespace Tests\DataFixtures\ORM\User;

use App\Domain\User\Entity\User;
use App\Domain\User\Entity\ValueObject\LastVisit;
use App\Domain\User\Entity\ValueObject\PasswordHashingOptions;
use Doctrine\Common\Persistence\ObjectManager;
use Faker\Generator;
use Tests\DataFixtures\Helper\DefaultUserPasswordGenerator;

class LoadUserForBan extends UserFixture
{
    public const REFERENCE_NAME = 'user-for-ban-test';

    private Generator $generator;
    private DefaultUserPasswordGenerator $passwordGenerator;

    public function __construct(Generator $generator, DefaultUserPasswordGenerator $passwordGenerator)
    {
        $this->generator = $generator;
        $this->passwordGenerator = $passwordGenerator;
    }

    public function load(ObjectManager $manager): void
    {
        $lastVisit = new LastVisit($this->generator->ipv4, new \DateTime());

        $user = new User(
            self::REFERENCE_NAME,
            self::REFERENCE_NAME.'@fishingsib.loc',
            $this->passwordGenerator->generate(),
            new PasswordHashingOptions(),
            $lastVisit
        );
        $user
            ->confirmEmail()
            ->updateGlobalRating(0)
            ->setForumUserId(self::getForumUserId());

        $this->addReference(self::REFERENCE_NAME, $user);
        $manager->persist($user);

        $manager->flush();
    }
}
