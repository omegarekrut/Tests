<?php

namespace Tests\DataFixtures\ORM\Record;

use App\Domain\Record\Gallery\Entity\Gallery;
use App\Domain\User\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Generator;
use Tests\DataFixtures\Helper\AuthorHelper;
use Tests\DataFixtures\Helper\MediaHelper;
use Tests\DataFixtures\ORM\LoadCategories;
use Tests\DataFixtures\ORM\User\LoadTestUser;

class LoadGalleryWithOwner extends Fixture implements DependentFixtureInterface
{
    public const REFERENCE_NAME = 'gallery-with-owner';

    private Generator $generator;
    private MediaHelper $mediaHelper;
    private AuthorHelper $authorHelper;

    public function __construct(\Faker\Generator $generator, MediaHelper $mediaHelper, AuthorHelper $authorHelper)
    {
        $this->generator = $generator;
        $this->mediaHelper = $mediaHelper;
        $this->authorHelper = $authorHelper;
    }

    public function load(ObjectManager $manager): void
    {
        $user = $this->getReference(LoadTestUser::USER_TEST);
        assert($user instanceof User);

        $gallery = new Gallery(
            $this->generator->realText(20),
            $this->generator->realText(200),
            $this->authorHelper->createFromUser($user),
            $this->getReference(LoadCategories::getRandReferenceNameForRootCategory(LoadCategories::ROOT_GALLERY)),
            $this->mediaHelper->createImage()
        );

        $manager->persist($gallery);
        $this->addReference(self::REFERENCE_NAME, $gallery);

        $manager->flush();
    }

    /**
     * @return string[]
     */
    public function getDependencies(): array
    {
        return [
            LoadCategories::class,
            LoadTestUser::class,
        ];
    }
}
