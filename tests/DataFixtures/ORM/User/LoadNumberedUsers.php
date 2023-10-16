<?php

namespace Tests\DataFixtures\ORM\User;

use App\Domain\User\Entity\User;
use App\Domain\User\Entity\ValueObject\LastVisit;
use App\Domain\User\Entity\ValueObject\PasswordHashingOptions;
use DateTime;
use Doctrine\Common\Persistence\ObjectManager;
use InvalidArgumentException;
use Tests\DataFixtures\Helper\DefaultUserPasswordGenerator;

class LoadNumberedUsers extends UserFixture
{
    protected const REFERENCE_PREFIX = 'user';

    private const COUNT = 30;

    private \Faker\Generator $generator;
    private \Faker\UniqueGenerator $uniqueGenerator;
    private DefaultUserPasswordGenerator $passwordGenerator;

    public function __construct(\Faker\Generator $generator, \Faker\UniqueGenerator $uniqueGenerator, DefaultUserPasswordGenerator $passwordGenerator)
    {
        $this->generator = $generator;
        $this->uniqueGenerator = $uniqueGenerator;
        $this->passwordGenerator = $passwordGenerator;
    }

    public static function getRandReferenceName(): string
    {
        return sprintf('%s-%d', static::REFERENCE_PREFIX, random_int(1, self::COUNT));
    }

    public static function getReferenceNameByNumber(int $numberOfUser): string
    {
        self::assertNumberOfUserLessThenCount($numberOfUser);

        return sprintf('%s-%d', static::REFERENCE_PREFIX, $numberOfUser);
    }

    public function load(ObjectManager $manager): void
    {
        $lastVisit = new LastVisit($this->generator->ipv4, new DateTime());

        for ($i = 1; $i <= self::COUNT; $i++) {
            $user = new User(
                $this->uniqueGenerator->userName,
                $this->uniqueGenerator->email,
                $this->passwordGenerator->generate(),
                new PasswordHashingOptions(),
                $lastVisit
            );

            $user
                ->rewriteGroup($this->generator->randomElement([
                    'user',
                    'admin',
                    'moderator',
                    'moderator_abm',
                ]))
                ->confirmEmail()
                ->setForumUserId(self::getForumUserId())
                ->updateGlobalRating($this->generator->randomDigit);

            $manager->persist($user);
            $this->addReference(sprintf('%s-%d', static::REFERENCE_PREFIX, $i), $user);
        }

        $manager->flush();
    }

    private static function assertNumberOfUserLessThenCount(int $numberOfUser): void
    {
        if ($numberOfUser > self::COUNT) {
            throw new InvalidArgumentException('Number of user can`t be bigger then their count');
        }
    }
}
