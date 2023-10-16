<?php

namespace Tests\DataFixtures\ORM\User;

use App\Domain\User\Entity\User;
use App\Domain\User\Entity\ValueObject\LastVisit;
use App\Domain\User\Entity\ValueObject\PasswordHashingOptions;
use Carbon\Carbon;
use DateTime;
use Doctrine\Common\Persistence\ObjectManager;
use Tests\DataFixtures\Helper\DefaultUserPasswordGenerator;

class LoadUserWithoutRatingRegisteredMoreThanTwoYearsAgo extends UserFixture
{
    public const REFERENCE_NAME = 'old-user-without-rating';

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

        Carbon::setTestNow(Carbon::now()->subYears(2)->subDay());

        try {
            $user = new User(
                'old-user-without-rating',
                'old-user-without-rating@fishingsib.ru',
                $this->passwordGenerator->generate(),
                new PasswordHashingOptions(),
                $lastVisit
            );
            $user
                ->confirmEmail()
                ->updateGlobalRating(0)
                ->setForumUserId(self::getForumUserId());

            $this->addReference(self::REFERENCE_NAME, $user);
            $manager->persist($user);

            $manager->flush();
        } finally {
            Carbon::setTestNow();
        }
    }
}
