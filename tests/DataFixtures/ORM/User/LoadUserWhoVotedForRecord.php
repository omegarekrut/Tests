<?php

namespace Tests\DataFixtures\ORM\User;

use App\Domain\Record\Common\Entity\Record;
use App\Domain\User\Entity\User;
use App\Domain\User\Entity\ValueObject\LastVisit;
use App\Domain\User\Entity\ValueObject\PasswordHashingOptions;
use App\Module\Voting\VoteStorage;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Faker\Generator;
use Tests\DataFixtures\Helper\DefaultUserPasswordGenerator;
use Tests\DataFixtures\ORM\Record\LoadArticles;

class LoadUserWhoVotedForRecord extends UserFixture implements DependentFixtureInterface
{
    public const REFERENCE_NAME = 'user-with-votes';
    private Generator $generator;
    private DefaultUserPasswordGenerator $passwordGenerator;
    private VoteStorage $voteStorage;

    public function __construct(\Faker\Generator $generator, DefaultUserPasswordGenerator $passwordGenerator, VoteStorage $voteStorage)
    {
        $this->generator = $generator;
        $this->passwordGenerator = $passwordGenerator;
        $this->voteStorage = $voteStorage;
    }

    public function load(ObjectManager $manager): void
    {
        $lastVisit = new LastVisit($this->generator->ipv4, new \DateTime());

        $voter = new User(
            'record-voter',
            'record-voter@fishingsib.loc',
            $this->passwordGenerator->generate(),
            new PasswordHashingOptions(),
            $lastVisit
        );
        $voter
            ->confirmEmail()
            ->updateGlobalRating(1)
            ->setForumUserId(self::getForumUserId());

        $this->addReference(self::REFERENCE_NAME, $voter);
        $manager->persist($voter);

        $manager->flush();

        /** @var Record $record */
        $record = $this->getReference(LoadArticles::getRandReferenceName());

        $this->voteStorage->addVote(1, $voter, $record, $voter->getLastVisit()->getLastVisitIp());
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
