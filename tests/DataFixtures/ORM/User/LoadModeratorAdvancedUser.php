<?php

namespace Tests\DataFixtures\ORM\User;

use App\Domain\User\Entity\User;
use App\Domain\User\Entity\ValueObject\LastVisit;
use App\Domain\User\Entity\ValueObject\PasswordHashingOptions;
use Doctrine\Common\Persistence\ObjectManager;
use Tests\DataFixtures\Helper\DefaultUserPasswordGenerator;
use Tests\DataFixtures\ORM\SingleReferenceFixtureInterface;

class LoadModeratorAdvancedUser extends UserFixture implements SingleReferenceFixtureInterface
{
    /** @deprecated Use {@link getReferenceName} */
    public const REFERENCE_NAME = 'user-moderator-abm';

    private \Faker\Generator $generator;
    private DefaultUserPasswordGenerator $passwordGenerator;

    public static function getReferenceName(): string
    {
        return self::REFERENCE_NAME;
    }

    public function __construct(\Faker\Generator $generator, DefaultUserPasswordGenerator $passwordGenerator)
    {
        $this->generator = $generator;
        $this->passwordGenerator = $passwordGenerator;
    }

    public function load(ObjectManager $manager): void
    {
        $lastVisit = new LastVisit($this->generator->ipv4, new \DateTime());

        $user = new User(
            'user-moderator-abm',
            'user-moderator-abm@gmail.com',
            $this->passwordGenerator->generate(),
            new PasswordHashingOptions(),
            $lastVisit
        );

        $user
            ->rewriteGroup('moderator_abm')
            ->confirmEmail()
            ->updateGlobalRating(20)
            ->setForumUserId(self::getForumUserId());

        $this->addReference(static::getReferenceName(), $user);

        $manager->persist($user);
        $manager->flush();
    }
}
