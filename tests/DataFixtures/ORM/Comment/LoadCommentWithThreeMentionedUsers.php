<?php

namespace Tests\DataFixtures\ORM\Comment;

use App\Domain\Record\Common\Entity\Record;
use App\Domain\User\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Faker\Generator;
use Ramsey\Uuid\Uuid;
use Tests\DataFixtures\Helper\AuthorHelper;
use Tests\DataFixtures\ORM\Record\LoadArticles;
use Tests\DataFixtures\ORM\User\LoadAdminUser;
use Tests\DataFixtures\ORM\User\LoadModeratorUser;
use Tests\DataFixtures\ORM\User\LoadTestUser;
use Tests\DataFixtures\ORM\User\LoadUserWithDotInUsername;

class LoadCommentWithThreeMentionedUsers extends Fixture implements DependentFixtureInterface
{
    public const REFERENCE_NAME = 'comment-with-mention-three-mentioned-users';

    private Generator $generator;
    private AuthorHelper $authorHelper;

    public function __construct(\Faker\Generator $generator, AuthorHelper $authorHelper)
    {
        $this->generator = $generator;
        $this->authorHelper = $authorHelper;
    }

    public function load(ObjectManager $manager): void
    {
        /** @var Record $record */
        $record = $this->getReference(LoadArticles::getRandReferenceName());
        /** @var User $userWithDotInLogin */
        $userWithDotInLogin = $this->getReference(LoadUserWithDotInUsername::REFERENCE_NAME);
        /** @var User $userTest */
        $userTest = $this->getReference(LoadTestUser::USER_TEST);
        /** @var User $admin */
        $admin = $this->getReference(LoadAdminUser::REFERENCE_NAME);
        /** @var User $userModerator */
        $userModerator = $this->getReference(LoadModeratorUser::REFERENCE_NAME);

        $author = $this->authorHelper->createFromUser($userModerator);

        $commentText = sprintf(
            'Comment mail@mail.com containing @%s mentions @%s, @%s',
            $userWithDotInLogin->getLogin(),
            $userTest->getLogin(),
            $admin->getLogin()
        );

        $comment = $record->addComment(Uuid::uuid4(), $this->generator->regexify('[A-Za-z0-9]{20}'), $commentText, $author);

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
        ];
    }
}
