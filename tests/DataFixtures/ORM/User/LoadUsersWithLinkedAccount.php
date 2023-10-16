<?php

namespace Tests\DataFixtures\ORM\User;

use App\Domain\User\Entity\User;
use App\Domain\User\Entity\ValueObject\LastVisit;
use App\Domain\User\Entity\ValueObject\PasswordHashingOptions;
use Doctrine\Common\Persistence\ObjectManager;
use Tests\DataFixtures\Helper\DefaultUserPasswordGenerator;

class LoadUsersWithLinkedAccount extends UserFixture
{
    protected const USER_WITH_USER_PROVIDER = 'user-with-user-provider';
    private const COUNT = 3;
    private \Faker\Generator $generator;
    private \Faker\UniqueGenerator $uniqueGenerator;
    private DefaultUserPasswordGenerator $passwordGenerator;

    public function __construct(\Faker\Generator $generator, \Faker\UniqueGenerator $uniqueGenerator, DefaultUserPasswordGenerator $passwordGenerator)
    {
        $this->generator = $generator;
        $this->uniqueGenerator = $uniqueGenerator;
        $this->passwordGenerator = $passwordGenerator;
    }

    public static function getRandReferenceName(): string
    {
        return sprintf('%s-%d', static::USER_WITH_USER_PROVIDER, random_int(1, self::COUNT));
    }

    public function load(ObjectManager $manager): void
    {
        $lastVisit = new LastVisit($this->generator->ipv4, new \DateTime());

        for ($i = 1; $i <= self::COUNT; $i++) {
            $user = new User(
                $this->uniqueGenerator->userName,
                $this->uniqueGenerator->email,
                $this->passwordGenerator->generate(),
                new PasswordHashingOptions(),
                $lastVisit
            );

            $user
                ->confirmEmail()
                ->setForumUserId(self::getForumUserId())
                ->attachLinkedAccount($this->uniqueGenerator->uuid, 'vkontakte', 'http://foo.bar/profile');

            $this->addReference(sprintf('%s-%d', static::USER_WITH_USER_PROVIDER, $i), $user);
            $manager->persist($user);
        }
        $manager->flush();
    }
}
