<?php

namespace Tests\DataFixtures\ORM\User;

use App\Domain\User\Entity\User;
use App\Domain\User\Entity\ValueObject\LastVisit;
use App\Domain\User\Entity\ValueObject\PasswordHashingOptions;
use App\Domain\User\Entity\ValueObject\Token;
use Doctrine\Common\Persistence\ObjectManager;
use Tests\DataFixtures\Helper\DefaultUserPasswordGenerator;

class LoadResetPasswordUser extends UserFixture
{
    public const USER_RESET_PASSWORD = 'user-reset-password';

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
            'user-reset-password',
            'user-reset-password@gmail.com',
            $this->passwordGenerator->generate(),
            new PasswordHashingOptions(),
            $lastVisit
        );

        $user
            ->confirmEmail()
            ->setForumUserId(self::getForumUserId())
            ->setResetPasswordToken(new Token(null, new \DateTime()));

        $this->addReference(self::USER_RESET_PASSWORD, $user);
        $manager->persist($user);

        $manager->flush();
    }
}
