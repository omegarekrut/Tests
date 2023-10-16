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
use Tests\DataFixtures\Helper\AuthorHelper;
use Tests\DataFixtures\ORM\Company\Company\LoadCompanyWithoutOwner;
use Tests\DataFixtures\ORM\Record\LoadArticles;
use Tests\DataFixtures\ORM\User\LoadAdminUser;
use Tests\DataFixtures\ORM\User\LoadModeratorUser;
use Tests\DataFixtures\ORM\User\LoadTestUser;
use Tests\DataFixtures\ORM\User\LoadUserWithDotInUsername;

class LoadCommentWithCompanyAuthorWithoutCompanyOwner extends Fixture implements DependentFixtureInterface
{
    public const REFERENCE_NAME = 'comment-with-company-author-without-company-owner';

    private Generator $generator;
    private AuthorHelper $authorHelper;

    public function __construct(Generator $generator, AuthorHelper $authorHelper)
    {
        $this->generator = $generator;
        $this->authorHelper = $authorHelper;
    }

    public function load(ObjectManager $manager): void
    {
        $company = $this->getReference(LoadCompanyWithoutOwner::REFERENCE_NAME);
        assert($company instanceof Company);

        $record = $this->getReference(LoadArticles::getRandReferenceName());
        assert($record instanceof Record);

        $userWithDotInLogin = $this->getReference(LoadUserWithDotInUsername::REFERENCE_NAME);
        assert($userWithDotInLogin instanceof User);

        $userTest = $this->getReference(LoadTestUser::USER_TEST);
        assert($userTest instanceof User);

        $admin = $this->getReference(LoadAdminUser::REFERENCE_NAME);
        assert($admin instanceof User);

        $userModerator = $this->getReference(LoadModeratorUser::REFERENCE_NAME);
        assert($userModerator instanceof User);

        $author = $this->authorHelper->createFromUser($userModerator);

        $commentText = sprintf(
            'Comment mail@mail.com containing @%s mentions @%s, @%s',
            $userWithDotInLogin->getLogin(),
            $userTest->getLogin(),
            $admin->getLogin()
        );

        $comment = $record->addComment(Uuid::uuid4(), $this->generator->regexify('[A-Za-z0-9]{20}'), $commentText, $author);
        assert($comment instanceof Comment);

        $comment->setCompanyAuthor($company);

        $this->addReference(self::REFERENCE_NAME, $comment);

        $manager->flush();
    }

    /**
     * @inheritdoc
     */
    public function getDependencies(): array
    {
        return [
            LoadUserWithDotInUsername::class,
            LoadArticles::class,
            LoadTestUser::class,
            LoadAdminUser::class,
            LoadModeratorUser::class,
            LoadCompanyWithoutOwner::class,
        ];
    }
}
