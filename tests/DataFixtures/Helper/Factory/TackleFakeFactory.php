<?php

namespace Tests\DataFixtures\Helper\Factory;

use App\Domain\Record\Tackle\Entity\Tackle;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Faker\Generator;
use Tests\DataFixtures\Helper\AuthorHelper;
use Tests\DataFixtures\Helper\FixtureHelperAssertions;
use Tests\DataFixtures\Helper\MediaHelper;
use Tests\DataFixtures\ORM\LoadCategories;
use Tests\DataFixtures\ORM\Record\LoadTackleBrands;

class TackleFakeFactory
{
    private $faker;
    private $authorHelper;
    private $mediaHelper;

    public function __construct(
        Generator $faker,
        AuthorHelper $authorHelper,
        MediaHelper $mediaHelper
    ) {
        $this->faker = $faker;
        $this->authorHelper = $authorHelper;
        $this->mediaHelper = $mediaHelper;
    }

    public function createFake(AbstractFixture $fixture): Tackle
    {
        FixtureHelperAssertions::assertFixtureDependsOnOtherFixture($fixture, LoadCategories::class);
        FixtureHelperAssertions::assertFixtureDependsOnOtherFixture($fixture, LoadTackleBrands::class);

        return new Tackle(
            $this->faker->realText(20),
            $this->faker->realText(),
            $this->authorHelper->chooseAuthor($fixture),
            $fixture->getReference(LoadCategories::getRandReferenceNameForRootCategory(LoadCategories::ROOT_TACKLE)),
            $fixture->getReference(LoadTackleBrands::getRandReferenceName()),
            $this->mediaHelper->createImage()
        );
    }
}
