<?php

namespace Tests\DataFixtures\ORM\User;

use App\Domain\User\Entity\User;
use App\Domain\User\Entity\ValueObject\LastVisit;
use App\Domain\User\Entity\ValueObject\PasswordHashingOptions;
use DateTime;
use Doctrine\Common\Persistence\ObjectManager;
use Tests\DataFixtures\Helper\DefaultUserPasswordGenerator;
use Tests\DataFixtures\ORM\SingleReferenceFixtureInterface;

class LoadMostActiveUser extends UserFixture implements SingleReferenceFixtureInterface
{
    /** @deprecated Use {@link getReferenceName} */
    public const USER_MOST_ACTIVE = 'user-most-active';

    private \Faker\Generator $generator;
    private DefaultUserPasswordGenerator $passwordGenerator;

    public static function getReferenceName(): string
    {
        return self::USER_MOST_ACTIVE;
    }

    public function __construct(\Faker\Generator $generator, DefaultUserPasswordGenerator $passwordGenerator)
    {
        $this->generator = $generator;
        $this->passwordGenerator = $passwordGenerator;
    }

    public function load(ObjectManager $manager): void
    {
        $lastVisit = new LastVisit($this->generator->ipv4, new DateTime());

        $user = new User(
            'most-active-user',
            'most-active-user@gmail.com',
            $this->passwordGenerator->generate(),
            new PasswordHashingOptions(),
            $lastVisit
        );

        $user
            ->rewriteProfileBasicInformationFromDTO((object) [
                'login' => $user->getLogin(),
                'email' => $user->getEmailAddress(),
                'showEmail' => true,
                'birthdate' => null,
                'name' => null,
                'city' => null,
                'gender' => null,
            ])
            ->confirmEmail()
            ->updateGlobalRating(10)
            ->setForumUserId(self::getForumUserId());

        $manager->persist($user);
        $this->addReference(self::getReferenceName(), $user);

        $manager->flush();
    }
}
