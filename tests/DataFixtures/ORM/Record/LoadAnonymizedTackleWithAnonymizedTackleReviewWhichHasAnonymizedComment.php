<?php

namespace Tests\DataFixtures\ORM\Record;

use App\Domain\Comment\Entity\Comment;
use App\Domain\Record\Tackle\Entity\Tackle;
use App\Module\Author\AnonymousAuthor;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Ramsey\Uuid\Uuid;
use Tests\DataFixtures\Helper\Factory\TackleReviewFakeFactory;
use Tests\DataFixtures\Helper\MediaHelper;
use Tests\DataFixtures\ORM\LoadCategories;
use Tests\DataFixtures\ORM\User\LoadMostActiveUser;
use Tests\DataFixtures\ORM\User\LoadNumberedUsers;

class LoadAnonymizedTackleWithAnonymizedTackleReviewWhichHasAnonymizedComment extends Fixture implements DependentFixtureInterface
{
    public const ANONYMIZED_TACKLE = 'anonymized-tackle';
    public const ANONYMIZED_TACKLE_REVIEW = 'anonymized-tackle-review';

    private \Faker\Generator $generator;
    private TackleReviewFakeFactory $tackleReviewFakeFactory;
    private MediaHelper $mediaHelper;

    public function __construct(\Faker\Generator $generator, TackleReviewFakeFactory $tackleReviewFakeFactory, MediaHelper $mediaHelper)
    {
        $this->generator = $generator;
        $this->tackleReviewFakeFactory = $tackleReviewFakeFactory;
        $this->mediaHelper = $mediaHelper;
    }

    public function load(ObjectManager $manager): void
    {
        $tackle = new Tackle(
            'anonymized-tackle',
            $this->generator->realText(),
            new AnonymousAuthor('anonymous-author'),
            $this->getReference(LoadCategories::getRandReferenceNameForRootCategory(LoadCategories::ROOT_TACKLE)),
            $this->getReference(LoadTackleBrands::getRandReferenceName()),
            $this->mediaHelper->createImage()
        );

        $reviewAuthor = new AnonymousAuthor('anonymous-review-author');
        $tackleReview = $this->tackleReviewFakeFactory->createFake($this, $tackle, $reviewAuthor);

        $commentAuthor = new AnonymousAuthor('anonymous-comment-author');
        $tackleReviewComment = new Comment(Uuid::uuid4(), 'someslug', 'comment', $tackleReview, $commentAuthor);

        $manager->persist($tackle);
        $manager->persist($tackleReview);
        $manager->persist($tackleReviewComment);

        $this->addReference(self::ANONYMIZED_TACKLE, $tackle);
        $this->addReference(self::ANONYMIZED_TACKLE_REVIEW, $tackleReview);

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            LoadTackleBrands::class,
            LoadCategories::class,
            LoadMostActiveUser::class,
            LoadNumberedUsers::class,
        ];
    }
}
