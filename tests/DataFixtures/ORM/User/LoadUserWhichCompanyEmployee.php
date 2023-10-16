<?php

namespace Tests\DataFixtures\ORM\User;

use App\Domain\User\Entity\User;
use App\Domain\User\Entity\ValueObject\LastVisit;
use App\Domain\User\Entity\ValueObject\PasswordHashingOptions;
use DateTime;
use Doctrine\Persistence\ObjectManager;
use Faker\Generator;
use Tests\DataFixtures\Helper\DefaultUserPasswordGenerator;

class LoadUserWhichCompanyEmployee extends UserFixture
{
    public const REFERENCE_NAME = 'user-which-company-employee';

    private Generator $generator;
    private DefaultUserPasswordGenerator $passwordGenerator;

    public function __construct(Generator $generator, DefaultUserPasswordGenerator $passwordGenerator)
    {
        $this->generator = $generator;
        $this->passwordGenerator = $passwordGenerator;
    }

    public function load(ObjectManager $manager): void
    {
        $lastVisit = new LastVisit($this->generator->ipv4, new DateTime());

        $user = new User(
            'user-which-company-employee',
            'user-which-company-employee@fishingsib.loc',
            $this->passwordGenerator->generate(),
            new PasswordHashingOptions(),
            $lastVisit
        );
        $user
            ->confirmEmail()
            ->setForumUserId(self::getForumUserId());

        $this->addReference(self::REFERENCE_NAME, $user);
        $manager->persist($user);

        $manager->flush();
    }
}
