<?php

namespace Tests\DataFixtures\ORM\Record\Tidings;

use App\Domain\Record\Tidings\Entity\Tidings;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Tests\DataFixtures\Helper\AuthorHelper;
use Tests\DataFixtures\Helper\MediaHelper;
use Tests\DataFixtures\Helper\RatingHelper;
use Tests\DataFixtures\ORM\Region\Region\LoadTestRegion;
use Tests\DataFixtures\ORM\User\LoadMostActiveUser;
use Tests\DataFixtures\ORM\User\LoadNumberedUsers;

class LoadTidingsWithRegion extends Fixture implements DependentFixtureInterface
{
    public const REFERENCE_NAME = 'tidings-with-region';

    private \Faker\Generator $generator;
    private AuthorHelper $authorHelper;
    private MediaHelper $mediaHelper;

    public function __construct(\Faker\Generator $generator, AuthorHelper $authorHelper, MediaHelper $mediaHelper)
    {
        $this->generator = $generator;
        $this->authorHelper = $authorHelper;
        $this->mediaHelper = $mediaHelper;
    }

    /**
     * @return string[]
     */
    public function getDependencies(): array
    {
        return [
            LoadNumberedUsers::class,
            LoadMostActiveUser::class,
            LoadTestRegion::class,
        ];
    }

    public function load(ObjectManager $manager): void
    {
        $tidings = new Tidings(
            $this->generator->realText(20),
            'Lorem ipsum dolor sit amet, black hole hyper отзыв перейти consectetur adipiscing elit. Morbi convallis sagittis bibendum.',
            $this->authorHelper->chooseAuthor($this)
        );

        $tidings->addImage($this->mediaHelper->createImage());
        RatingHelper::setRating($tidings);

        $tidings->rewriteRegion($this->getReference(LoadTestRegion::REFERENCE_NAME));

        $manager->persist($tidings);

        $this->addReference(self::REFERENCE_NAME, $tidings);

        $manager->flush();
    }
}
