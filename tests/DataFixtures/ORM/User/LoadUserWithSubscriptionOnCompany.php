<?php

namespace Tests\DataFixtures\ORM\User;

use App\Domain\Company\Entity\Company;
use App\Domain\User\Entity\User;
use App\Domain\User\Entity\ValueObject\LastVisit;
use App\Domain\User\Entity\ValueObject\PasswordHashingOptions;
use DateTime;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Faker\Generator;
use Tests\DataFixtures\Helper\DefaultUserPasswordGenerator;
use Tests\DataFixtures\ORM\Company\Company\LoadCompanyWithSubscriber;

class LoadUserWithSubscriptionOnCompany extends UserFixture implements DependentFixtureInterface
{
    public const REFERENCE_NAME = 'user-with-subscription-on-company';

    private Generator $generator;
    private DefaultUserPasswordGenerator $passwordGenerator;

    public function __construct(Generator $generator, DefaultUserPasswordGenerator $passwordGenerator)
    {
        $this->generator = $generator;
        $this->passwordGenerator = $passwordGenerator;
    }

    public static function getReferenceName(): string
    {
        return self::REFERENCE_NAME;
    }

    public function load(ObjectManager $manager): void
    {
        $company = $this->getReference(LoadCompanyWithSubscriber::REFERENCE_NAME);
        assert($company instanceof Company);

        $lastVisit = new LastVisit($this->generator->ipv4, new DateTime());

        $subscriber = new User(
            'subscriber',
            'subscriber@fishingsib.ru',
            $this->passwordGenerator->generate(),
            new PasswordHashingOptions(),
            $lastVisit
        );
        $subscriber
            ->confirmEmail()
            ->updateGlobalRating(11)
            ->setForumUserId(self::getForumUserId());
        $subscriber->subscribeOnCompany($company);

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
            LoadCompanyWithSubscriber::class,
        ];
    }
}
