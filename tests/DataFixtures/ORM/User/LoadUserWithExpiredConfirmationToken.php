<?php

namespace Tests\DataFixtures\ORM\User;

use App\Domain\User\Entity\User;
use App\Domain\User\Entity\ValueObject\LastVisit;
use App\Domain\User\Entity\ValueObject\PasswordHashingOptions;
use App\Domain\User\Entity\ValueObject\Token;
use Carbon\Carbon;
use Doctrine\Common\Persistence\ObjectManager;
use Tests\DataFixtures\Helper\DefaultUserPasswordGenerator;

class LoadUserWithExpiredConfirmationToken extends UserFixture
{
    public const REFERENCE_NAME = 'user-with-expired-confirmation-email';

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
            'user-with-expired-confirmation-email',
            'user-with-expired-confirmation-email@fishingsib.loc',
            $this->passwordGenerator->generate(),
            new PasswordHashingOptions(),
            $lastVisit
        );

        $user
            ->updateGlobalRating(11)
            ->setForumUserId(self::getForumUserId())
            ->getEmail()
                ->bounce()
                ->setConfirmationToken(new Token('expired-token', Carbon::now()->subMonths(5)));

        $this->addReference(self::REFERENCE_NAME, $user);
        $manager->persist($user);

        $manager->flush();
    }
}
