<?php

namespace Tests\Functional\Domain\Record\Article\Command\Handler;

use App\Domain\Category\Entity\Category;
use App\Domain\Record\Article\Command\UpdateArticleCommand;
use App\Domain\Record\Article\Entity\Article;
use App\Util\ImageStorage\Collection\ImageWithRotationAngleCollection;
use App\Util\ImageStorage\Image;
use App\Util\ImageStorage\ImageTransformerFactory;
use App\Util\ImageStorage\ImageWithRotationAngle;
use Carbon\Carbon;
use Tests\DataFixtures\ORM\LoadCategories;
use Tests\DataFixtures\ORM\Record\Articles\LoadArticlesForSemanticLinks;
use Tests\Functional\TestCase;

/**
 * @group article
 */
class UpdateArticleHandlerTest extends TestCase
{
    private Article $article;
    private Category $articleCategory;

    protected function setUp(): void
    {
        parent::setUp();

        $referenceRepository = $this->loadFixtures([
            LoadArticlesForSemanticLinks::class,
            LoadCategories::class,
        ])->getReferenceRepository();

        $this->article = $referenceRepository->getReference(LoadArticlesForSemanticLinks::REFERENCE_NAME);
        $this->articleCategory = $referenceRepository->getReference(LoadCategories::getRandReferenceNameForRootCategory(LoadCategories::ROOT_ARTICLES));
    }

    protected function tearDown(): void
    {
        unset(
            $this->article,
            $this->articleCategory
        );

        parent::tearDown();
    }

    public function testArticleIsUpdatedAndSaved(): void
    {
        $imageWithRotationAngle = new ImageWithRotationAngle('image.jpg', 0);
        $command = $this->createUpdateArticleCommand($imageWithRotationAngle);

        $now = Carbon::create();
        Carbon::setTestNow($now);

        try {
            $this->getCommandBus()->handle($command);

            $this->assertEquals($command->category, $this->article->getCategory());
            $this->assertEquals($command->title, $this->article->getTitle());
            $this->assertEquals($command->text, $this->article->getText());
            $this->assertCount(1, $this->article->getImages());
            $this->assertEquals(new Image($imageWithRotationAngle->getFilename()), $this->article->getImages()[0]);
            $this->assertEquals(false, $this->article->isHtmlAllowed());
            $this->assertEquals($now, $this->article->getUpdatedAt());
        } finally {
            Carbon::setTestNow();
        }
    }

    public function testArticleIsUpdatedAndSavedWithRotation(): void
    {
        $imageWithRotationAngle = new ImageWithRotationAngle('image.jpg', 90);
        $command = $this->createUpdateArticleCommand($imageWithRotationAngle);

        $this->getCommandBus()->handle($command);

        $this->assertEquals($command->category, $this->article->getCategory());
        $this->assertEquals($command->title, $this->article->getTitle());
        $this->assertEquals(
            'Article text updated https://transformer image name.fhserv.ru/sandbox/transformer transformer image name name__rsu-1024-800.jpg?hash=c89e5e46',
            $this->article->getText()
        );
        $this->assertCount(1, $this->article->getImages());
        $this->assertEquals(new Image('transformer image name.jpg'), $this->article->getImages()[0]);
        $this->assertEquals(false, $this->article->isHtmlAllowed());
    }

    private function createUpdateArticleCommand(ImageWithRotationAngle $imageWithRotationAngle): UpdateArticleCommand
    {
        $command = new UpdateArticleCommand($this->article);
        $command->category = $this->articleCategory;
        $command->title = $this->article->getTitle().' updated';
        $command->text = 'Article text updated '.$this->createImageUrlWithResize2Universal($imageWithRotationAngle);
        $command->images = new ImageWithRotationAngleCollection([$imageWithRotationAngle]);

        return $command;
    }

    private function createImageUrlWithResize2Universal(ImageWithRotationAngle $imageWithRotationAngle): string
    {
        return (string) $this->getContainer()->get(ImageTransformerFactory::class)
            ->create(new Image($imageWithRotationAngle->getFilename()))
            ->withResize2Universal(1024, 800);
    }
}
