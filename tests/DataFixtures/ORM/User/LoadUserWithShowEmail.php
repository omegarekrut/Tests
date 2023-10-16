<?php

namespace Tests\DataFixtures\ORM\User;

use App\Domain\User\Entity\User;
use App\Domain\User\Entity\ValueObject\LastVisit;
use App\Domain\User\Entity\ValueObject\PasswordHashingOptions;
use DateTime;
use Doctrine\Common\Persistence\ObjectManager;
use Tests\DataFixtures\Helper\DefaultUserPasswordGenerator;

class LoadUserWithShowEmail extends UserFixture
{
    public const USER_WITH_SHOW_EMAIL = 'user-with-show-email';

    private \Faker\Generator $generator;
    private DefaultUserPasswordGenerator $passwordGenerator;

    public function __construct(\Faker\Generator $generator, DefaultUserPasswordGenerator $passwordGenerator)
    {
        $this->generator = $generator;
        $this->passwordGenerator = $passwordGenerator;
    }

    public function load(ObjectManager $manager): void
    {
        $lastVisit = new LastVisit($this->generator->ipv4, new DateTime());

        $user = new User(
            'user-with-show-email',
            'user-with-show-email@gmail.com',
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
            ->setForumUserId(self::getForumUserId());

        $manager->persist($user);
        $this->addReference(self::USER_WITH_SHOW_EMAIL, $user);

        $manager->flush();
    }
}
