<?php

namespace Tests\Functional\Domain\Record\Article\Command\Admin\Handler;

use App\Domain\Category\Entity\Category;
use App\Domain\Record\Article\Command\Admin\CreateArticleCommand;
use App\Domain\Record\Article\Entity\Article;
use App\Domain\Record\Article\Repository\ArticleRepository;
use App\Domain\User\Entity\User;
use App\Util\ImageStorage\Collection\ImageCollection;
use App\Util\ImageStorage\Collection\ImageWithRotationAngleCollection;
use App\Util\ImageStorage\Image;
use App\Util\ImageStorage\ImageWithRotationAngle;
use Tests\DataFixtures\ORM\LoadCategories;
use Tests\DataFixtures\ORM\User\LoadTestUser;
use Tests\Functional\TestCase;

/**
 * @group article
 */
class CreateArticleHandlerTest extends TestCase
{
    /** @var User */
    private $user;
    /** @var Category */
    private $articleCategory;
    /** @var ArticleRepository */
    private $articleRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $referenceRepository = $this->loadFixtures([
            LoadTestUser::class,
            LoadCategories::class,
        ])->getReferenceRepository();

        $this->user = $referenceRepository->getReference(LoadTestUser::USER_TEST);
        $this->articleCategory = $referenceRepository->getReference(LoadCategories::getRandReferenceNameForRootCategory(LoadCategories::ROOT_ARTICLES));
        $this->articleRepository = $this->getEntityManager()->getRepository(Article::class);
    }

    protected function tearDown(): void
    {
        unset(
            $this->user,
            $this->articleCategory,
            $this->articleRepository
        );

        parent::tearDown();
    }

    public function testArticleIsCreatedAndSaved(): void
    {
        $command = $this->createArticleCommand(new ImageWithRotationAngle('image.jpg', 0));

        $this->getCommandBus()->handle($command);

        $articleList = $this->articleRepository->findAllByTitle($command->title);
        $this->assertCount(1, $articleList);

        /** @var Article $actualArticle */
        $actualArticle = current($articleList);

        $this->assertEquals($command->category, $actualArticle->getCategory());
        $this->assertEquals($command->title, $actualArticle->getTitle());
        $this->assertEquals($command->preview, $actualArticle->getPreview());
        $this->assertEquals($command->text, $actualArticle->getText());
        $this->assertEquals($command->priority, $actualArticle->getPriority());
        $this->assertEquals(new ImageCollection([new Image('image.jpg')]), $actualArticle->getImages());
        $this->assertEquals($command->isHtmlAllowed(), $actualArticle->isHtmlAllowed());
    }

    public function testArticleIsCreatedAndSavedWithRotation(): void
    {
        $command = $this->createArticleCommand(new ImageWithRotationAngle('image.jpg', 90));

        $this->getCommandBus()->handle($command);

        $articleList = $this->articleRepository->findAllByTitle($command->title);
        $this->assertCount(1, $articleList);

        /** @var Article $actualArticle */
        $actualArticle = current($articleList);

        $this->assertEquals($command->category, $actualArticle->getCategory());
        $this->assertEquals($command->title, $actualArticle->getTitle());
        $this->assertEquals($command->preview, $actualArticle->getPreview());
        $this->assertEquals($command->text, $actualArticle->getText());
        $this->assertCount(1, $actualArticle->getImages());
        $this->assertEquals(new Image('transformer image name.jpg'), $actualArticle->getImages()[0]);
        $this->assertEquals($command->isHtmlAllowed(), $actualArticle->isHtmlAllowed());
    }

    private function createArticleCommand(ImageWithRotationAngle $imageWithRotationAngle): CreateArticleCommand
    {
        $command = new CreateArticleCommand($this->user, true);
        $command->category = $this->articleCategory;
        $command->title = 'Article title created';
        $command->preview = 'Article preview';
        $command->text = 'Article text';
        $command->priority = 6;
        $command->images = new ImageWithRotationAngleCollection([$imageWithRotationAngle]);

        return $command;
    }
}
