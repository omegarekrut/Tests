<?php

namespace Tests\DataFixtures\Helper\Factory;

use App\Domain\Record\Tackle\Entity\Tackle;
use App\Domain\Record\Tackle\Entity\TackleReview;
use App\Module\Author\AuthorInterface;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Faker\Generator;
use Tests\DataFixtures\Helper\FixtureHelperAssertions;
use Tests\DataFixtures\Helper\RatingHelper;
use Tests\DataFixtures\ORM\LoadCategories;

class TackleReviewFakeFactory
{
    private $faker;

    public function __construct(Generator $faker)
    {
        $this->faker = $faker;
    }

    public function createFake(AbstractFixture $fixture, Tackle $tackle, AuthorInterface $author): TackleReview
    {
        FixtureHelperAssertions::assertFixtureDependsOnOtherFixture($fixture, LoadCategories::class);

        $faker = $this->faker;

        $tackleReview = new TackleReview(
            $faker->realText(20),
            $faker->realText(),
            $author,
            $fixture->getReference(LoadCategories::getReferenceRootName(LoadCategories::ROOT_TACKLE_REVIEWS)),
            $tackle,
            $faker->randomDigitNotNull(),
            $faker->realText(200),
            $faker->realText(200),
            $faker->realText(100)
        );

        RatingHelper::setRating($tackleReview);

        return $tackleReview;
    }
}
