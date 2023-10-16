<?php

namespace Tests\DataFixtures\ORM\Record\Tidings;

use App\Domain\Record\Tidings\Entity\Tidings;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Faker\Generator;
use Tests\DataFixtures\Helper\AuthorHelper;
use Tests\DataFixtures\Helper\CommentHelper;
use Tests\DataFixtures\Helper\MediaHelper;
use Tests\DataFixtures\Helper\RatingHelper;
use Tests\DataFixtures\ORM\User\LoadMostActiveUser;
use Tests\DataFixtures\ORM\User\LoadNumberedUsers;

class LoadNumberedTidings extends Fixture implements DependentFixtureInterface
{
    protected const REFERENCE_PREFIX = 'tidings';
    protected const COUNT = 30;

    private Generator $generator;
    private CommentHelper $commentHelper;
    private MediaHelper $mediaHelper;
    private AuthorHelper $authorHelper;

    public function __construct(\Faker\Generator $generator, CommentHelper $commentHelper, MediaHelper $mediaHelper, AuthorHelper $authorHelper)
    {
        $this->generator = $generator;
        $this->commentHelper = $commentHelper;
        $this->mediaHelper = $mediaHelper;
        $this->authorHelper = $authorHelper;
    }

    public static function getRandReferenceName(): string
    {
        return sprintf('%s-%d', static::REFERENCE_PREFIX, rand(1, static::COUNT));
    }

    public function load(ObjectManager $manager): void
    {
        for ($i = 1; $i <= static::COUNT; $i++) {
            $tidings = $this->getTidings($manager, $this->generator, $this->authorHelper, $this->mediaHelper, $this->commentHelper);
            $this->addReference(sprintf('%s-%d', static::REFERENCE_PREFIX, $i), $tidings);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            LoadNumberedUsers::class,
            LoadMostActiveUser::class,
        ];
    }

    protected function getText(Generator $faker): string
    {
        return $faker->realText();
    }

    protected function getTidings(
        ObjectManager $manager,
        Generator $faker,
        AuthorHelper $authorHelper,
        MediaHelper $mediaHelper,
        CommentHelper $commentHelper
    ): Tidings
    {
        $tidings = new Tidings(
            $faker->realText(20),
            $this->getText($faker),
            $authorHelper->chooseAuthor($this)
        );
        $tidings
            ->addImage($mediaHelper->createImage())
            ->addImage($mediaHelper->createImage());
        RatingHelper::setRating($tidings);
        $commentHelper->addComments($this, $tidings);

        $manager->persist($tidings);

        return $tidings;
    }
}
