<?php

namespace Tests\DataFixtures\ORM\Comment;

use App\Domain\Comment\Entity\Comment;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Faker\Generator;
use Ramsey\Uuid\Uuid;

class LoadAnswersToComments extends Fixture implements DependentFixtureInterface
{
    public const REFERENCE_NAME = 'answer-to-comment';
    public const COMMENTS_REFS = [
        LoadCommentWithMentionedUser::REFERENCE_NAME,
        LoadCommentWithMentioningHimself::REFERENCE_NAME,
        LoadCommentWithoutUrls::REFERENCE_NAME,
        LoadCommentWithThreeMentionedUsers::REFERENCE_NAME,
    ];

    private Generator $generator;

    public function __construct(\Faker\Generator $generator)
    {
        $this->generator = $generator;
    }

    public function load(ObjectManager $manager): void
    {
        foreach (self::COMMENTS_REFS as $key => $commentRef) {
            $comment = $this->getReference($commentRef);
            assert($comment instanceof Comment);

            $answer = new Comment(Uuid::uuid4(), $this->generator->regexify('[A-Za-z0-9]{20}'), $this->generator->realText(), $comment->getRecord(), $comment->getAuthor());
            $answer->rewriteParentComment($comment);

            $manager->persist($answer);
            $this->addReference(sprintf('%s-%d', self::REFERENCE_NAME, ++$key), $answer);
        }

        $manager->flush();
    }

    public static function getRandReferenceName(): string
    {
        return sprintf('%s-%d', self::REFERENCE_NAME, rand(1, count(self::COMMENTS_REFS)));
    }

    /**
     * @inheritdoc
     */
    public function getDependencies(): array
    {
        return [
            LoadCommentWithMentionedUser::class,
            LoadCommentWithMentioningHimself::class,
            LoadCommentWithoutUrls::class,
            LoadCommentWithThreeMentionedUsers::class,
        ];
    }
}
