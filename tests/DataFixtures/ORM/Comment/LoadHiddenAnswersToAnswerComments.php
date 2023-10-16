<?php

namespace Tests\DataFixtures\ORM\Comment;

use App\Domain\Comment\Entity\Comment;
use App\Domain\User\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Faker\Generator;
use Ramsey\Uuid\Uuid;
use Tests\DataFixtures\Helper\AuthorHelper;
use Tests\DataFixtures\ORM\User\LoadAdminUser;
use Tests\DataFixtures\ORM\User\LoadMostActiveUser;
use Tests\DataFixtures\ORM\User\LoadNumberedUsers;

class LoadHiddenAnswersToAnswerComments extends Fixture implements DependentFixtureInterface
{
    public const REFERENCE_NAME = 'hidden-answer-to-answer-comment';

    private Generator $generator;
    private AuthorHelper $authorHelper;

    public function __construct(\Faker\Generator $generator, AuthorHelper $authorHelper)
    {
        $this->generator = $generator;
        $this->authorHelper = $authorHelper;
    }

    public function load(ObjectManager $manager): void
    {
        $admin = $this->getReference(LoadAdminUser::REFERENCE_NAME);
        assert($admin instanceof User);
        $answerRef = sprintf('%s-%d', LoadAnswersToComments::REFERENCE_NAME, 1);
        $answer = $this->getReference($answerRef);

        for ($i = 1; $i <= count(LoadAnswersToComments::COMMENTS_REFS); $i++) {
            assert($answer instanceof Comment);

            $answerToAnswerComment = new Comment(
                Uuid::uuid4(),
                $this->generator->regexify('[A-Za-z0-9]{20}'),
                $this->generator->realText(),
                $answer->getRecord(),
                $this->authorHelper->chooseAuthor($this)
            );
            $answerToAnswerComment->rewriteParentComment($answer);
            $answerToAnswerComment->deactivateBy($admin);

            $manager->persist($answerToAnswerComment);
            $answer = $answerToAnswerComment;
            $this->addReference(sprintf('%s-%d', self::REFERENCE_NAME, $i), $answerToAnswerComment);
        }

        $manager->flush();
    }

    public static function getRootReferenceName(): string
    {
        return sprintf('%s-1', self::REFERENCE_NAME);
    }

    /**
     * @inheritdoc
     */
    public function getDependencies(): array
    {
        return [
            LoadAnswersToComments::class,
            LoadNumberedUsers::class,
            LoadMostActiveUser::class,
            LoadAdminUser::class,
        ];
    }
}
