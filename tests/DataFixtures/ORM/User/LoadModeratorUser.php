<?php

namespace Tests\DataFixtures\ORM\User;

use App\Domain\User\Entity\User;
use App\Domain\User\Entity\ValueObject\LastVisit;
use App\Domain\User\Entity\ValueObject\PasswordHashingOptions;
use DateTime;
use Doctrine\Common\Persistence\ObjectManager;
use Faker\Generator;
use Tests\DataFixtures\Helper\DefaultUserPasswordGenerator;
use Tests\DataFixtures\ORM\SingleReferenceFixtureInterface;

class LoadModeratorUser extends UserFixture implements SingleReferenceFixtureInterface
{
    /** @deprecated Use {@link getReferenceName} */
    public const REFERENCE_NAME = 'user-moderator';

    private Generator $generator;
    private DefaultUserPasswordGenerator $passwordGenerator;

    public static function getReferenceName(): string
    {
        return self::REFERENCE_NAME;
    }

    public function __construct(Generator $generator, DefaultUserPasswordGenerator $passwordGenerator)
    {
        $this->generator = $generator;
        $this->passwordGenerator = $passwordGenerator;
    }

    public function load(ObjectManager $manager): void
    {
        $lastVisit = new LastVisit($this->generator->ipv4, new DateTime());

        $userModerator = new User(
            'user-moderator',
            'user-moderator@gmail.com',
            $this->passwordGenerator->generate(),
            new PasswordHashingOptions(),
            $lastVisit
        );

        $userModerator
            ->rewriteGroup('moderator')
            ->confirmEmail()
            ->updateGlobalRating(20)
            ->setForumUserId(self::getForumUserId());

        $manager->persist($userModerator);
        $manager->flush();

        $this->addReference(static::getReferenceName(), $userModerator);
    }
}
