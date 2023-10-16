<?php

namespace Tests\DataFixtures\ORM\User;

use App\Domain\User\Entity\User;
use App\Domain\User\Entity\ValueObject\Avatar;
use App\Domain\User\Entity\ValueObject\LastVisit;
use App\Domain\User\Entity\ValueObject\PasswordHashingOptions;
use App\Util\ImageStorage\Image;
use App\Util\ImageStorage\ValueObject\ImageCroppingParameters;
use DateTime;
use Doctrine\Persistence\ObjectManager;
use Faker\Generator;
use Tests\DataFixtures\Helper\DefaultUserPasswordGenerator;
use Tests\DataFixtures\ORM\SingleReferenceFixtureInterface;

class LoadUserWithAvatar extends UserFixture implements SingleReferenceFixtureInterface
{
    /** @deprecated Use {@link getReferenceName} */
    public const REFERENCE_NAME = 'user-with-avatar';

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

        $user = new User(
            'user-with-avatar',
            'user-with-avatar@fishingsib.loc',
            $this->passwordGenerator->generate(),
            new PasswordHashingOptions(),
            $lastVisit
        );
        $user
            ->confirmEmail()
            ->setForumUserId(self::getForumUserId());

        $user->setAvatar(new Avatar(new Image('avatar-image.jpg'), new ImageCroppingParameters(50, 50, 100, 100)));

        $this->addReference(self::getReferenceName(), $user);
        $manager->persist($user);

        $manager->flush();
    }
}
