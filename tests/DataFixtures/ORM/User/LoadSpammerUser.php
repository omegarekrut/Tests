<?php

namespace Tests\DataFixtures\ORM\User;

use App\Domain\User\Entity\User;
use App\Domain\User\Entity\ValueObject\LastVisit;
use App\Domain\User\Entity\ValueObject\PasswordHashingOptions;
use Doctrine\Common\Persistence\ObjectManager;
use Tests\DataFixtures\Helper\DefaultUserPasswordGenerator;

class LoadSpammerUser extends UserFixture
{
    public const REFERENCE_NAME = 'user-spammer';

    private \Faker\Generator $generator;
    private \Faker\UniqueGenerator $uniqueGenerator;
    private DefaultUserPasswordGenerator $passwordGenerator;

    public function __construct(\Faker\Generator $generator, \Faker\UniqueGenerator $uniqueGenerator, DefaultUserPasswordGenerator $passwordGenerator)
    {
        $this->generator = $generator;
        $this->uniqueGenerator = $uniqueGenerator;
        $this->passwordGenerator = $passwordGenerator;
    }

    public function load(ObjectManager $manager): void
    {
        $lastVisit = new LastVisit($this->generator->ipv4, new \DateTime());

        $userSpammer = new User(
            $this->uniqueGenerator->username,
            $this->uniqueGenerator->email,
            $this->passwordGenerator->generate(),
            new PasswordHashingOptions(),
            $lastVisit
        );
        $userSpammer
            ->confirmEmail()
            ->updateGlobalRating(20)
            ->setForumUserId(self::getForumUserId());

        $manager->persist($userSpammer);
        $this->addReference(static::REFERENCE_NAME, $userSpammer);

        $manager->flush();
    }
}
