<?php

namespace Tests\Functional\Domain\Record\Article\Command\Handler;

use App\Domain\Category\Entity\Category;
use App\Domain\Company\Entity\Company;
use App\Domain\Record\Article\Command\CreateArticleCommand;
use App\Domain\Record\Article\Entity\Article;
use App\Domain\Record\Article\Repository\ArticleRepository;
use App\Domain\User\Entity\User;
use App\Util\ImageStorage\Collection\ImageWithRotationAngleCollection;
use App\Util\ImageStorage\Image;
use App\Util\ImageStorage\ImageTransformerFactory;
use App\Util\ImageStorage\ImageWithRotationAngle;
use Tests\DataFixtures\ORM\Company\Company\LoadAquaMotorcycleShopsCompany;
use Tests\DataFixtures\ORM\LoadCategories;
use Tests\DataFixtures\ORM\User\LoadUserWithAvatar;
use Tests\Functional\TestCase;

/**
 * @group article
 */
class CreateArticleHandlerTest extends TestCase
{
    private User $user;
    private Category $articleCategory;
    private ArticleRepository $articleRepository;
    private Company $company;

    protected function setUp(): void
    {
        parent::setUp();

        $referenceRepository = $this->loadFixtures([
            LoadUserWithAvatar::class,
            LoadCategories::class,
            LoadAquaMotorcycleShopsCompany::class,
        ])->getReferenceRepository();

        $this->user = $referenceRepository->getReference(LoadUserWithAvatar::REFERENCE_NAME);
        $this->articleCategory = $referenceRepository->getReference(LoadCategories::getRandReferenceNameForRootCategory(LoadCategories::ROOT_ARTICLES));
        $this->articleRepository = $this->getEntityManager()->getRepository(Article::class);
        $this->company = $referenceRepository->getReference(LoadAquaMotorcycleShopsCompany::REFERENCE_NAME);
    }

    protected function tearDown(): void
    {
        unset(
            $this->user,
            $this->articleCategory,
            $this->articleRepository,
            $this->company
        );

        parent::tearDown();
    }

    public function testArticleIsCreatedAndSaved(): void
    {
        $imageWithRotationAngle = new ImageWithRotationAngle('image.jpg', 0);
        $command = $this->createArticleCommand($imageWithRotationAngle);

        $this->getCommandBus()->handle($command);

        $articleList = $this->articleRepository->findAllByTitle($command->title);
        $this->assertCount(1, $articleList);

        $actualArticle = current($articleList);
        assert($actualArticle instanceof Article);

        $this->assertEquals($command->category, $actualArticle->getCategory());
        $this->assertEquals($command->title, $actualArticle->getTitle());
        $this->assertEquals($command->text, $actualArticle->getText());
        $this->assertCount(1, $actualArticle->getImages());
        $this->assertEquals(new Image($imageWithRotationAngle->getFilename()), $actualArticle->getImages()[0]);
        $this->assertEquals(false, $actualArticle->isHtmlAllowed());
    }

    public function testArticleIsCreatedByCompanyAuthor(): void
    {
        $imageWithRotationAngle = new ImageWithRotationAngle('image.jpg', 90);
        $command = $this->createArticleCommand($imageWithRotationAngle);
        $command->author = $this->company->getOwner();
        $command->companyAuthor = $this->company;

        $this->getCommandBus()->handle($command);

        $actualArticle = $this->articleRepository->findLastArticleForUser($command->author);
        assert($actualArticle instanceof Article);

        $this->assertEquals($command->companyAuthor, $actualArticle->getCompanyAuthor());
        $this->assertEquals($command->companyAuthor->getName(), $actualArticle->getCompanyAuthorName());
    }

    public function testArticleIsCreatedAndSavedWithRotation(): void
    {
        $imageWithRotationAngle = new ImageWithRotationAngle('image.jpg', 90);
        $command = $this->createArticleCommand($imageWithRotationAngle);

        $this->getCommandBus()->handle($command);

        $articleList = $this->articleRepository->findAllByTitle($command->title);
        $this->assertCount(1, $articleList);

        $actualArticle = current($articleList);
        assert($actualArticle instanceof Article);

        $this->assertEquals($command->category, $actualArticle->getCategory());
        $this->assertEquals($command->title, $actualArticle->getTitle());
        $this->assertEquals(
            'Article text https://transformer image name.fhserv.ru/sandbox/transformer transformer image name name__rsu-1024-800.jpg?hash=c89e5e46',
            $actualArticle->getText()
        );
        $this->assertCount(1, $actualArticle->getImages());
        $this->assertEquals(new Image('transformer image name.jpg'), $actualArticle->getImages()[0]);
        $this->assertEquals(false, $actualArticle->isHtmlAllowed());
    }

    private function createArticleCommand(ImageWithRotationAngle $imageWithRotationAngle): CreateArticleCommand
    {
        $command = new CreateArticleCommand($this->user);
        $command->category = $this->articleCategory;
        $command->title = 'Article title created';
        $command->text = 'Article text '.$this->createImageUrlWithResize2Universal($imageWithRotationAngle);
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
