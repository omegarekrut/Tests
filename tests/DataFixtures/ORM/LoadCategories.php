<?php

namespace Tests\DataFixtures\ORM;

use App\Domain\Category\Entity\Category;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Faker\Generator as FakerGenerator;

class LoadCategories extends Fixture
{
    public const ROOT_ARTICLES = Category::ROOT_ARTICLES_SLUG;
    public const ROOT_GALLERY = Category::ROOT_GALLERY_SLUG;
    public const ROOT_VIDEO = Category::ROOT_VIDEO_SLUG;
    public const ROOT_TACKLE = Category::ROOT_TACKLES_SLUG;
    public const ROOT_TACKLE_REVIEWS = Category::ROOT_TACKLE_REVIEWS;

    public const REFERENCE_ROOT_ARTICLE_TACKLE = 'category-articles-child-10';

    /**
     * @var string[][]|int[][] $format [$slug => [$title, $childrenCount]]
     */
    private static $rootImportantCategoryConfig = [
        self::ROOT_ARTICLES => ['Записи', 9],
        self::ROOT_GALLERY => ['Рыболовная фотогалерея', 10],
        self::ROOT_VIDEO => ['Видео о рыбалке', 10],
        self::ROOT_TACKLE => ['Снасти', 10],
        self::ROOT_TACKLE_REVIEWS => ['Отзывы о снастях', 0],
    ];

    private FakerGenerator $generator;

    public function __construct(\Faker\Generator $generator)
    {
        $this->generator = $generator;
    }

    public static function getReferenceRootName(string $rootCategory): string
    {
        return sprintf('category-%s', $rootCategory);
    }

    public static function getRandReferenceNameForRootCategory(string $rootCategory): string
    {
        $maxId = self::getCountChildForRoot($rootCategory);

        if ($rootCategory === self::ROOT_ARTICLES) {
            $maxId++;
        }

        return sprintf('%s-child-%d', self::getReferenceRootName($rootCategory), random_int(1, $maxId));
    }

    private static function getCountChildForRoot(string $rootCategory): int
    {
        $information = self::$rootImportantCategoryConfig[$rootCategory] ?? [];

        return (int) ($information[count($information) - 1] ?? 0);
    }

    public function load(ObjectManager $manager): void
    {
        foreach (self::$rootImportantCategoryConfig as $slug => $rootCategoryConfig) {
            [$title, $childrenCount] = $rootCategoryConfig;
            $rootCategory = new Category($title, $this->generator->realText(255), $slug);

            $manager->persist($rootCategory);
            $rootReferenceName = self::getReferenceRootName($slug);
            $this->addReference($rootReferenceName, $rootCategory);

            $manager->flush(); // flushing is required after persisting each category to properly reorder the tree

            for ($i = 1; $i <= $childrenCount; $i++) {
                $child = new Category(
                    $this->generator->realText(20),
                    $this->generator->realText(255),
                    $this->generator->unique()->slug(1, false),
                    $rootCategory
                );

                $manager->persist($child);
                $manager->flush();

                $this->addReference(sprintf('%s-child-%d', $rootReferenceName, $i), $child);
            }
        }

        $this->generator->unique(true);

        $this->createTacklesCategory($manager);
    }

    /**
     * Required child category of articles
     */
    private function createTacklesCategory(ObjectManager $manager): void
    {
        $articleSubCategory = new Category(
            $this->generator->realText(20),
            $this->generator->realText(255),
            Category::SNASTI_ARTICLES_SLUG,
            $this->getReference(self::getReferenceRootName(self::ROOT_ARTICLES))
        );

        $manager->persist($articleSubCategory);
        $manager->flush();

        $this->addReference(self::REFERENCE_ROOT_ARTICLE_TACKLE, $articleSubCategory);
    }
}
