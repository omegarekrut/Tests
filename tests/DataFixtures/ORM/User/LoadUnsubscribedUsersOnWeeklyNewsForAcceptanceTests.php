<?php

namespace Tests\DataFixtures\ORM\User;

use App\Domain\User\Entity\User;
use App\Domain\User\Entity\ValueObject\LastVisit;
use App\Domain\User\Entity\ValueObject\PasswordHashingOptions;
use DateTime;
use Doctrine\Common\Persistence\ObjectManager;
use Tests\DataFixtures\Helper\DefaultUserPasswordGenerator;

class LoadUnsubscribedUsersOnWeeklyNewsForAcceptanceTests extends UserFixture
{
    private const NUMBER_OF_USERS = 5;
    private const REFERENCE_PREFIX = 'unsubscribed-user';

    private \Faker\Generator $generator;
    private DefaultUserPasswordGenerator $passwordGenerator;

    public function __construct(\Faker\Generator $generator, DefaultUserPasswordGenerator $passwordGenerator)
    {
        $this->generator = $generator;
        $this->passwordGenerator = $passwordGenerator;
    }

    public function load(ObjectManager $manager): void
    {
        for ($i = 1; $i <= self::NUMBER_OF_USERS; $i++) {
            $lastVisit = new LastVisit($this->generator->ipv4, new DateTime());

            $user = new User(
                sprintf('%s%d', self::REFERENCE_PREFIX, $i),
                sprintf('%s%d@fishingsib.loc', self::REFERENCE_PREFIX, $i),
                $this->passwordGenerator->generate(),
                new PasswordHashingOptions(),
                $lastVisit
            );

            $user
                ->confirmEmail()
                ->updateGlobalRating(11)
                ->setForumUserId(self::getForumUserId())
                ->unsubscribeFromWeeklyNewsletter();

            $name = sprintf('%s-%d', self::REFERENCE_PREFIX, $i);
            $this->addReference($name, $user);
            $manager->persist($user);

            $manager->flush();
        }
    }
}
