<?php

namespace Tests\DataFixtures\ORM\Record\Gallery;

use App\Domain\Category\Entity\Category;
use App\Domain\Record\Gallery\Entity\Gallery;
use App\Domain\User\Entity\User;
use App\Module\Author\AuthorInterface;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Generator;
use Ramsey\Uuid\Uuid;
use Tests\DataFixtures\Helper\MediaHelper;
use Tests\DataFixtures\ORM\LoadCategories;
use Tests\DataFixtures\ORM\SingleReferenceFixtureInterface;
use Tests\DataFixtures\ORM\User\LoadMostActiveUser;
use Tests\DataFixtures\ORM\User\LoadTestUser;

class LoadGalleryWithComment extends Fixture implements DependentFixtureInterface, SingleReferenceFixtureInterface
{
    private Generator $generator;
    private MediaHelper $mediaHelper;

    public static function getReferenceName(): string
    {
        return 'gallery-with-comment';
    }

    public function __construct(Generator $generator, MediaHelper $mediaHelper)
    {
        $this->generator = $generator;
        $this->mediaHelper = $mediaHelper;
    }

    public function load(ObjectManager $manager): void
    {
        $galleryAuthor = $this->getReference(LoadTestUser::getReferenceName());
        assert($galleryAuthor instanceof User);

        $category = $this->getReference(LoadCategories::getRandReferenceNameForRootCategory(LoadCategories::ROOT_GALLERY));
        assert($category instanceof Category);

        $gallery = new Gallery(
            $this->generator->realText(20),
            $this->generator->realText(100),
            $galleryAuthor,
            $category,
            $this->mediaHelper->createImage()
        );

        $manager->persist($gallery);

        $commentAuthor = $this->getReference(LoadMostActiveUser::getReferenceName());
        assert($commentAuthor instanceof AuthorInterface);

        $gallery->addComment(
            Uuid::uuid4(),
            $this->generator->regexify('[A-Za-z0-9]{20}'),
            $this->generator->realText(),
            $commentAuthor,
        );

        $manager->flush();

        $this->addReference(self::getReferenceName(), $gallery);
    }

    /**
     * @return string[]
     */
    public function getDependencies(): array
    {
        return [
            LoadCategories::class,
            LoadMostActiveUser::class,
            LoadTestUser::class,
        ];
    }
}
