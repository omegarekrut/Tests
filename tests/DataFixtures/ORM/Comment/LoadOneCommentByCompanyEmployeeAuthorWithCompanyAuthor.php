<?php

namespace Tests\DataFixtures\ORM\Comment;

use App\Domain\Comment\Entity\Comment;
use App\Domain\Company\Entity\Company;
use App\Domain\Record\Common\Entity\Record;
use App\Domain\User\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Faker\Generator;
use Ramsey\Uuid\Uuid;
use Tests\DataFixtures\ORM\Company\Company\LoadCompanyWithEmployee;
use Tests\DataFixtures\ORM\Record\LoadNews;
use Tests\DataFixtures\ORM\User\LoadUserWhichCompanyEmployee;

class LoadOneCommentByCompanyEmployeeAuthorWithCompanyAuthor extends Fixture implements DependentFixtureInterface
{
    public const REFERENCE_NAME = 'one-comment-by-company-employee-author-with-company-author';

    private Generator $generator;

    public function __construct(Generator $generator)
    {
        $this->generator = $generator;
    }

    public function load(ObjectManager $manager): void
    {
        $company = $this->getReference(LoadCompanyWithEmployee::REFERENCE_NAME);
        assert($company instanceof Company);

        $author = $this->getReference(LoadUserWhichCompanyEmployee::REFERENCE_NAME);
        assert($author instanceof User);

        $record = $this->getReference(LoadNews::getRandReferenceName());
        assert($record instanceof Record);

        $comment = new Comment(
            Uuid::uuid4(),
            $this->generator->regexify('[A-Za-z0-9]{20}'),
            $this->generator->realText(),
            $record,
            $author
        );
        $comment->setCompanyAuthor($company);

        $manager->persist($comment);

        $this->addReference(self::REFERENCE_NAME, $comment);

        $manager->flush();
    }

    /**
     * @inheritDoc
     */
    public function getDependencies(): array
    {
        return [
            LoadNews::class,
            LoadCompanyWithEmployee::class,
            LoadUserWhichCompanyEmployee::class,
        ];
    }
}
