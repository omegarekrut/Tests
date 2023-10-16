<?php

namespace Tests\DataFixtures\ORM\Record;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Tests\DataFixtures\Helper\AuthorHelper;
use Tests\DataFixtures\Helper\CommentHelper;
use Tests\DataFixtures\Helper\Factory\TackleReviewFakeFactory;
use Tests\DataFixtures\ORM\LoadCategories;
use Tests\DataFixtures\ORM\User\LoadMostActiveUser;
use Tests\DataFixtures\ORM\User\LoadNumberedUsers;

class LoadTackleReviews extends Fixture implements DependentFixtureInterface
{
    private const REFERENCE_PREFIX = 'tackle-review';
    private const COUNT = 30;

    private CommentHelper $commentHelper;
    private TackleReviewFakeFactory $tackleReviewFakeFactory;
    private AuthorHelper $authorHelper;

    public function __construct(CommentHelper $commentHelper, TackleReviewFakeFactory $tackleReviewFakeFactory, AuthorHelper $authorHelper)
    {
        $this->commentHelper = $commentHelper;
        $this->tackleReviewFakeFactory = $tackleReviewFakeFactory;
        $this->authorHelper = $authorHelper;
    }

    public static function getRandReferenceName(): string
    {
        return sprintf('%s-%d', self::REFERENCE_PREFIX, rand(1, self::COUNT));
    }

    public function load(ObjectManager $manager): void
    {
        for ($i = 1; $i <= self::COUNT; $i++) {
            $tackle = $this->getReference(LoadTackles::getRandReferenceName());

            $tackleReview = $this->tackleReviewFakeFactory->createFake($this, $tackle, $this->authorHelper->chooseAuthor($this));

            $this->commentHelper->addComments($this, $tackleReview);

            $manager->persist($tackleReview);

            $this->addReference(sprintf('%s-%d', self::REFERENCE_PREFIX, $i), $tackleReview);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            LoadCategories::class,
            LoadTackles::class,
            LoadNumberedUsers::class,
            LoadMostActiveUser::class,
        ];
    }
}
