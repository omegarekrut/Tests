<?php

namespace Tests\DataFixtures\ORM\User;

use App\Domain\User\Entity\User;
use App\Domain\User\Entity\ValueObject\LastVisit;
use App\Domain\User\Entity\ValueObject\PasswordHashingOptions;
use Doctrine\Common\Persistence\ObjectManager;
use Tests\DataFixtures\Helper\DefaultUserPasswordGenerator;

class LoadUnsubscribedUserOnWeeklyNews extends UserFixture
{
    public const NAME = 'user-unsubscribe-weekly-news';

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
            'unsubscribed-user',
            'unsubscribed-user@fishingsib.loc',
            $this->passwordGenerator->generate(),
            new PasswordHashingOptions(),
            $lastVisit
        );

        $user
            ->confirmEmail()
            ->updateGlobalRating(11)
            ->setForumUserId(self::getForumUserId())
            ->unsubscribeFromWeeklyNewsletter();

        $this->addReference(self::NAME, $user);
        $manager->persist($user);

        $manager->flush();
    }
}
