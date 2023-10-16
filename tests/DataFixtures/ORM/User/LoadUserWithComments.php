<?php

namespace Tests\DataFixtures\ORM\User;

use App\Domain\Record\Common\Entity\Record;
use App\Domain\User\Entity\User;
use App\Domain\User\Entity\ValueObject\LastVisit;
use App\Domain\User\Entity\ValueObject\PasswordHashingOptions;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Ramsey\Uuid\Uuid;
use Tests\DataFixtures\Helper\DefaultUserPasswordGenerator;
use Tests\DataFixtures\ORM\Record\LoadArticles;

class LoadUserWithComments extends UserFixture implements DependentFixtureInterface
{
    public const REFERENCE_NAME = 'user-with-comments';

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
            'user-with-comments',
            'user-with-comments@fishingsib.loc',
            $this->passwordGenerator->generate(),
            new PasswordHashingOptions(),
            $lastVisit
        );
        $user
            ->confirmEmail()
            ->updateGlobalRating(1)
            ->setForumUserId(self::getForumUserId());

        $this->addReference(self::REFERENCE_NAME, $user);
        $manager->persist($user);

        /** @var Record $record */
        $record = $this->getReference(LoadArticles::getRandReferenceName());
        $record->addComment(Uuid::uuid4(), $this->generator->regexify('[A-Za-z0-9]{20}'), 'First comment text', $user);
        $record->addComment(Uuid::uuid4(), $this->generator->regexify('[A-Za-z0-9]{20}'), 'Second comment text', $user);
        $record->addComment(Uuid::uuid4(), $this->generator->regexify('[A-Za-z0-9]{20}'), 'Third comment text', $user);

        $manager->flush();
    }

    /**
     * @inheritdoc
     */
    public function getDependencies(): array
    {
        return [
            LoadArticles::class,
        ];
    }
}
