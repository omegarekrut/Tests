<?php

namespace Tests\DataFixtures\ORM\User;

use App\Domain\User\Entity\User;
use App\Domain\User\Entity\ValueObject\LastVisit;
use App\Domain\User\Entity\ValueObject\PasswordHashingOptions;
use DateTime;
use Doctrine\Persistence\ObjectManager;
use Faker\Generator;
use Tests\DataFixtures\Helper\DefaultUserPasswordGenerator;

class LoadManySimpleUsersForOwnCompany extends UserFixture
{
    public const USER_PREFIX_REFERENCE = 'simple-company-owner';
    public const COUNT_OF_USERS_FOR_LOAD = 30;

    private Generator $generator;
    private DefaultUserPasswordGenerator $passwordGenerator;

    public function __construct(Generator $generator, DefaultUserPasswordGenerator $passwordGenerator)
    {
        $this->generator = $generator;
        $this->passwordGenerator = $passwordGenerator;
    }

    public function load(ObjectManager $manager): void
    {
        $this->createSimpleUsers($manager);

        $manager->flush();
    }

    private function createSimpleUsers(ObjectManager $manager): void
    {
        foreach (range(0, self::COUNT_OF_USERS_FOR_LOAD) as $indexOfUser) {
            $userNameReference = self::USER_PREFIX_REFERENCE.'-'.$indexOfUser;
            $user = new User(
                $userNameReference,
                $userNameReference.'@fishingsib.ru',
                $this->passwordGenerator->generate(),
                new PasswordHashingOptions(),
                new LastVisit($this->generator->ipv4, new DateTime())
            );

            $user->confirmEmail()
                ->updateGlobalRating(11)
                ->setForumUserId(self::getForumUserId());
            $this->addReference($userNameReference, $user);
            $manager->persist($user);
        }
    }
}
