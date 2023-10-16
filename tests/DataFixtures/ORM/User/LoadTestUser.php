<?php

namespace Tests\DataFixtures\ORM\User;

use App\Domain\User\Entity\User;
use App\Domain\User\Entity\ValueObject\LastVisit;
use App\Domain\User\Entity\ValueObject\PasswordHashingOptions;
use Doctrine\Common\Persistence\ObjectManager;
use Tests\DataFixtures\Helper\DefaultUserPasswordGenerator;
use Tests\DataFixtures\ORM\SingleReferenceFixtureInterface;

class LoadTestUser extends UserFixture implements SingleReferenceFixtureInterface
{
    /** @deprecated Use {@link getReferenceName} */
    public const USER_TEST = 'user-test';
    private DefaultUserPasswordGenerator $defaultUserPasswordGenerator;
    private \Faker\Generator $generator;

    public static function getReferenceName(): string
    {
        return self::USER_TEST;
    }

    public function __construct(DefaultUserPasswordGenerator $defaultUserPasswordGenerator, \Faker\Generator $generator)
    {
        $this->defaultUserPasswordGenerator = $defaultUserPasswordGenerator;
        $this->generator = $generator;
    }

    public function load(ObjectManager $manager): void
    {
        $lastVisit = new LastVisit($this->generator->ipv4, new \DateTime());

        $test = new User(
            'test',
            'test@fishingsib.ru',
            $this->defaultUserPasswordGenerator->generate(),
            new PasswordHashingOptions(),
            $lastVisit
        );
        $test
            ->confirmEmail()
            ->updateGlobalRating(11)
            ->setForumUserId(self::getForumUserId());

        $this->addReference(static::getReferenceName(), $test);
        $manager->persist($test);

        $manager->flush();
    }
}
