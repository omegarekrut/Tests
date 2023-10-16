<?php

namespace Tests\DataFixtures\ORM\Comment;

use App\Domain\Comment\Entity\Comment;
use App\Domain\Record\Common\Entity\Record;
use App\Domain\User\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Faker\Generator;
use Ramsey\Uuid\Uuid;
use Tests\DataFixtures\ORM\Record\LoadArticles;
use Tests\DataFixtures\ORM\User\LoadUserWithSuspiciousLoginFromBannedAccount;

class LoadSpamComment extends Fixture implements DependentFixtureInterface
{
    public const REFERENCE_NAME = 'spam-comment';

    private Generator $generator;

    public function __construct(\Faker\Generator $generator)
    {
        $this->generator = $generator;
    }

    public function load(ObjectManager $manager): void
    {
        $record = $this->getReference(LoadArticles::getRandReferenceName());
        assert($record instanceof Record);

        $author = $this->getReference(LoadUserWithSuspiciousLoginFromBannedAccount::REFERENCE_NAME);
        assert($author instanceof User);

        $comment = new Comment(
            Uuid::uuid4(),
            $this->generator->regexify('[A-Za-z0-9]{20}'),
            'Spam comment. Url: http://kupi.ru',
            $record,
            $author
        );

        $manager->persist($comment);
        $this->addReference(self::REFERENCE_NAME, $comment);

        $manager->flush();
    }

    /**
     * @inheritdoc
     */
    public function getDependencies(): array
    {
        return [
            LoadArticles::class,
            LoadUserWithSuspiciousLoginFromBannedAccount::class,
        ];
    }
}
