<?php

namespace Tests\DataFixtures\ORM\Comment;

use App\Domain\Comment\Entity\Comment;
use App\Domain\Company\Entity\Company;
use App\Domain\Record\Common\Entity\Record;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Generator;
use Ramsey\Uuid\Uuid;
use Tests\DataFixtures\ORM\Company\Company\LoadCompanyWithDifferentRecordsByCompanyAuthor;
use Tests\DataFixtures\ORM\Record\LoadNews;

class LoadOneCommentByCompanyAuthor extends Fixture implements DependentFixtureInterface
{
    public const REFERENCE_NAME = 'comment-by-company-author';

    private Generator $generator;

    public function __construct(Generator $generator)
    {
        $this->generator = $generator;
    }

    public function load(ObjectManager $manager): void
    {
        $company = $this->getReference(LoadCompanyWithDifferentRecordsByCompanyAuthor::REFERENCE_NAME);
        assert($company instanceof Company);

        $record = $this->getReference(LoadNews::getRandReferenceName());
        assert($record instanceof Record);

        $author = $company->getOwner();

        $comment = new Comment(
            Uuid::uuid4(),
            $this->generator->regexify('[A-Za-z0-9]{20}'),
            'Comment containing link http://google.com',
            $record,
            $author
        );
        $comment->setCompanyAuthor($company);

        $manager->persist($comment);
        $manager->flush();

        $this->addReference(self::REFERENCE_NAME, $comment);
    }

    /**
     * @inheritDoc
     */
    public function getDependencies(): array
    {
        return [
            LoadNews::class,
            LoadCompanyWithDifferentRecordsByCompanyAuthor::class,
        ];
    }
}
