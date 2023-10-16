<?php

namespace Tests\Functional\Domain\Record\Article\Command\Admin\Handler;

use App\Domain\Category\Entity\Category;
use App\Domain\Record\Article\Command\Admin\UpdateArticleCommand;
use App\Domain\Record\Article\Entity\Article;
use App\Util\ImageStorage\Collection\ImageCollection;
use App\Util\ImageStorage\Collection\ImageWithRotationAngleCollection;
use App\Util\ImageStorage\Image;
use App\Util\ImageStorage\ImageWithRotationAngle;
use Carbon\Carbon;
use Tests\DataFixtures\ORM\LoadCategories;
use Tests\DataFixtures\ORM\Record\LoadArticles;
use Tests\Functional\TestCase;

/**
 * @group Article
 */
class UpdateArticleHandlerTest extends TestCase
{
    /** @var Article */
    private $article;
    /** @var Category */
    private $articleCategory;

    protected function setUp(): void
    {
        parent::setUp();

        $referenceRepository = $this->loadFixtures([
            LoadArticles::class,
            LoadCategories::class,
        ])->getReferenceRepository();

        /** @var Article $article */
        $this->article = $referenceRepository->getReference(LoadArticles::getRandReferenceName());
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

    public function testArticleIsChanged(): void
    {
        $command = $this->createUpdateArticleCommand(new ImageWithRotationAngle('image.jpg', 0));

        $now = Carbon::create();
        Carbon::setTestNow($now);

        try {
            $this->getCommandBus()->handle($command);

            $this->assertEquals($command->category, $this->article->getCategory());
            $this->assertEquals($command->title, $this->article->getTitle());
            $this->assertEquals($command->preview, $this->article->getPreview());
            $this->assertEquals($command->text, $this->article->getText());
            $this->assertEquals($command->priority, $this->article->getPriority());
            $this->assertEquals(new ImageCollection([new Image('image.jpg')]), $this->article->getImages());
            $this->assertEquals($now, $this->article->getUpdatedAt());
        } finally {
            Carbon::setTestNow();
        }
    }

    public function testArticleIsChangedWithRotation(): void
    {
        $imageWithRotationAngle = new ImageWithRotationAngle('image.jpg', 90);
        $command = $this->createUpdateArticleCommand($imageWithRotationAngle);

        $this->getCommandBus()->handle($command);

        $this->assertEquals($command->category, $this->article->getCategory());
        $this->assertEquals($command->title, $this->article->getTitle());
        $this->assertEquals($command->preview, $this->article->getPreview());
        $this->assertEquals($command->text, $this->article->getText());
        $this->assertEquals($command->priority, $this->article->getPriority());
        $this->assertEquals(new Image('transformer image name.jpg'), $this->article->getImages()[0]);
    }

    private function createUpdateArticleCommand(ImageWithRotationAngle $imageWithRotationAngle): UpdateArticleCommand
    {
        $command = new UpdateArticleCommand($this->article);
        $command->category = $this->articleCategory;
        $command->title = 'new article title';
        $command->preview = 'new article preview';
        $command->text = 'new article text';
        $command->priority = $this->article->getPriority() + 1;
        $command->images = new ImageWithRotationAngleCollection([$imageWithRotationAngle]);

        return $command;
    }
}
