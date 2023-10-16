<?php

namespace Tests\DataFixtures\ORM\Record;

use App\Domain\Record\Gallery\Entity\Gallery;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Faker\Generator;
use Tests\DataFixtures\Helper\AuthorHelper;
use Tests\DataFixtures\Helper\CommentHelper;
use Tests\DataFixtures\Helper\MediaHelper;
use Tests\DataFixtures\Helper\RatingHelper;
use Tests\DataFixtures\ORM\LoadCategories;
use Tests\DataFixtures\ORM\User\LoadMostActiveUser;
use Tests\DataFixtures\ORM\User\LoadNumberedUsers;

class LoadGallery extends Fixture implements DependentFixtureInterface
{
    private const REFERENCE_PREFIX = 'gallery';
    public const COUNT = 40;

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
        return sprintf('%s-%d', self::REFERENCE_PREFIX, rand(1, self::COUNT));
    }

    public function load(ObjectManager $manager): void
    {
        for ($i = 1; $i <= self::COUNT; $i++) {
            $gallery = new Gallery(
                $this->generator->realText(20),
                $this->generator->realText(200),
                $this->authorHelper->chooseAuthor($this),
                $this->getReference(LoadCategories::getRandReferenceNameForRootCategory(LoadCategories::ROOT_GALLERY)),
                $this->mediaHelper->createImage()
            );
            RatingHelper::setRating($gallery);
            $this->commentHelper->addComments($this, $gallery);

            $manager->persist($gallery);
            $this->addReference(sprintf('%s-%d', self::REFERENCE_PREFIX, $i), $gallery);
        }

        $manager->flush();
    }

    /**
     * @return string[]
     */
    public function getDependencies(): array
    {
        return [
            LoadCategories::class,
            LoadNumberedUsers::class,
            LoadMostActiveUser::class,
        ];
    }
}
