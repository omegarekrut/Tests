<?php

namespace Tests\DataFixtures\ORM\User;

use App\Domain\User\Entity\User;
use App\Domain\User\Entity\ValueObject\LastVisit;
use App\Domain\User\Entity\ValueObject\PasswordHashingOptions;
use DateTime;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Tests\DataFixtures\Helper\DefaultUserPasswordGenerator;

class LoadUserWithSubscriptionOnUser extends UserFixture implements DependentFixtureInterface
{
    public const REFERENCE_NAME = 'user-with-subscription-on-user';

    private \Faker\Generator $generator;
    private DefaultUserPasswordGenerator $passwordGenerator;

    public function __construct(\Faker\Generator $generator, DefaultUserPasswordGenerator $passwordGenerator)
    {
        $this->generator = $generator;
        $this->passwordGenerator = $passwordGenerator;
    }

    public function load(ObjectManager $manager): void
    {
        $user = $this->getReference(LoadTestUser::USER_TEST);
        assert($user instanceof User);

        $lastVisit = new LastVisit($this->generator->ipv4, new DateTime());

        $subscriber = new User(
            'user-with-subscription-on-user',
            'user-with-subscription-on-user@fishingsib.ru',
            $this->passwordGenerator->generate(),
            new PasswordHashingOptions(),
            $lastVisit,
        );

        $subscriber->subscribeOnUser($user);

        $this->addReference(self::REFERENCE_NAME, $subscriber);

        $manager->persist($subscriber);
        $manager->flush();
    }

    /**
     * @return string[]
     */
    public function getDependencies(): array
    {
        return [
            LoadTestUser::class,
        ];
    }
}
