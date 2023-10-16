<?php

namespace Tests\Controller\Record;

use App\Domain\Record\Article\Entity\Article;
use App\Domain\Record\Gallery\Entity\Gallery;
use App\Domain\Record\Tidings\Entity\Tidings;
use App\Domain\Record\Video\Entity\Video;
use App\Domain\User\Entity\User;
use Tests\Controller\TestCase;
use Tests\DataFixtures\ORM\Record\Articles\LoadArticleByCompanyAuthor;
use Tests\DataFixtures\ORM\Record\Articles\LoadSimpleArticle;
use Tests\DataFixtures\ORM\Record\Gallery\LoadGalleryByCompanyAuthor;
use Tests\DataFixtures\ORM\Record\LoadGalleryWithOwner;
use Tests\DataFixtures\ORM\Record\Tidings\LoadSimpleTidings;
use Tests\DataFixtures\ORM\Record\Tidings\LoadTidingsByCompanyAuthor;
use Tests\DataFixtures\ORM\Record\Video\LoadSimpleVideo;
use Tests\DataFixtures\ORM\Record\Video\LoadVideoByCompanyAuthor;
use Tests\DataFixtures\ORM\User\LoadAdminUser;
use Tests\DataFixtures\ORM\User\LoadModeratorUser;
use Tests\DataFixtures\ORM\User\LoadUserWithoutRecords;

class SeeMenuButtonsControllerTest extends TestCase
{
    private const EDIT_BUTTON_TEXT = 'Редактировать';
    private const DELETE_BUTTON_TEXT = 'Удалить';

    private Article $articleByCompanyAuthor;
    private Article $articleByAuthor;
    private Tidings $tidingsByCompanyAuthor;
    private Tidings $tidingsByAuthor;
    private Video $videoByCompanyAuthor;
    private Video $videoByAuthor;
    private Gallery $galleryByCompanyAuthor;
    private Gallery $galleryByAuthor;
    private User $admin;
    private User $moderator;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $referenceRepository = $this->loadFixtures([
            LoadArticleByCompanyAuthor::class,
            LoadSimpleArticle::class,
            LoadTidingsByCompanyAuthor::class,
            LoadSimpleTidings::class,
            LoadVideoByCompanyAuthor::class,
            LoadSimpleVideo::class,
            LoadGalleryByCompanyAuthor::class,
            LoadGalleryWithOwner::class,
            LoadAdminUser::class,
            LoadModeratorUser::class,
            LoadUserWithoutRecords::class,
        ])->getReferenceRepository();

        $articleByCompanyAuthor = $referenceRepository->getReference(LoadArticleByCompanyAuthor::REFERENCE_NAME);
        assert($articleByCompanyAuthor instanceof Article);
        $this->articleByCompanyAuthor = $articleByCompanyAuthor;

        $articleByAuthor = $referenceRepository->getReference(LoadSimpleArticle::REFERENCE_NAME);
        assert($articleByAuthor instanceof Article);
        $this->articleByAuthor = $articleByAuthor;

        $tidingsByCompanyAuthor = $referenceRepository->getReference(LoadTidingsByCompanyAuthor::REFERENCE_NAME);
        assert($tidingsByCompanyAuthor instanceof Tidings);
        $this->tidingsByCompanyAuthor = $tidingsByCompanyAuthor;

        $tidingsByAuthor = $referenceRepository->getReference(LoadSimpleTidings::REFERENCE_NAME);
        assert($tidingsByAuthor instanceof Tidings);
        $this->tidingsByAuthor = $tidingsByAuthor;

        $videoByCompanyAuthor = $referenceRepository->getReference(LoadVideoByCompanyAuthor::REFERENCE_NAME);
        assert($videoByCompanyAuthor instanceof Video);
        $this->videoByCompanyAuthor = $videoByCompanyAuthor;

        $videoByAuthor = $referenceRepository->getReference(LoadSimpleVideo::REFERENCE_NAME);
        assert($videoByAuthor instanceof Video);
        $this->videoByAuthor = $videoByAuthor;

        $galleryByCompanyAuthor = $referenceRepository->getReference(LoadGalleryByCompanyAuthor::REFERENCE_NAME);
        assert($galleryByCompanyAuthor instanceof Gallery);
        $this->galleryByCompanyAuthor = $galleryByCompanyAuthor;

        $galleryByAuthor = $referenceRepository->getReference(LoadGalleryWithOwner::REFERENCE_NAME);
        assert($galleryByAuthor instanceof Gallery);
        $this->galleryByAuthor = $galleryByAuthor;

        $admin = $referenceRepository->getReference(LoadAdminUser::REFERENCE_NAME);
        assert($admin instanceof User);
        $this->admin = $admin;

        $moderator = $referenceRepository->getReference(LoadModeratorUser::REFERENCE_NAME);
        assert($moderator instanceof User);
        $this->moderator = $moderator;

        $user = $referenceRepository->getReference(LoadUserWithoutRecords::REFERENCE_NAME);
        assert($user instanceof User);
        $this->user = $user;
    }

    public function testAdminSeeEditAndDeleteButtons()
    {
        $browser = $this->getBrowser()
            ->loginUser($this->admin);

        $page = $browser->request(
            'GET',
            sprintf('/articles/view/%s/', $this->articleByCompanyAuthor->getId())
        );

        $this->assertStringContainsString(self::EDIT_BUTTON_TEXT, $page->html());
        $this->assertStringContainsString(self::DELETE_BUTTON_TEXT, $page->html());

        $page = $browser->request(
            'GET',
            sprintf('/tidings/view/%s/', $this->tidingsByCompanyAuthor->getId())
        );

        $this->assertStringContainsString(self::EDIT_BUTTON_TEXT, $page->html());
        $this->assertStringContainsString(self::DELETE_BUTTON_TEXT, $page->html());

        $page = $browser->request(
            'GET',
            sprintf('/video/view/%s/', $this->videoByCompanyAuthor->getId())
        );

        $this->assertStringContainsString(self::EDIT_BUTTON_TEXT, $page->html());
        $this->assertStringContainsString(self::DELETE_BUTTON_TEXT, $page->html());

        $page = $browser->request(
            'GET',
            sprintf('/gallery/view/%s/', $this->galleryByCompanyAuthor->getId())
        );
    }

    public function testModeratorSeeEditAndDeleteButtons()
    {
        $browser = $this->getBrowser()
            ->loginUser($this->moderator);

        $page = $browser->request(
            'GET',
            sprintf('/articles/view/%s/', $this->articleByCompanyAuthor->getId())
        );

        $this->assertStringContainsString(self::EDIT_BUTTON_TEXT, $page->html());
        $this->assertStringContainsString(self::DELETE_BUTTON_TEXT, $page->html());

        $page = $browser->request(
            'GET',
            sprintf('/tidings/view/%s/', $this->tidingsByCompanyAuthor->getId())
        );

        $this->assertStringContainsString(self::EDIT_BUTTON_TEXT, $page->html());
        $this->assertStringContainsString(self::DELETE_BUTTON_TEXT, $page->html());

        $page = $browser->request(
            'GET',
            sprintf('/video/view/%s/', $this->videoByCompanyAuthor->getId())
        );

        $this->assertStringContainsString(self::EDIT_BUTTON_TEXT, $page->html());
        $this->assertStringContainsString(self::DELETE_BUTTON_TEXT, $page->html());

        $page = $browser->request(
            'GET',
            sprintf('/gallery/view/%s/', $this->galleryByCompanyAuthor->getId())
        );

        $this->assertStringContainsString(self::EDIT_BUTTON_TEXT, $page->html());
        $this->assertStringContainsString(self::DELETE_BUTTON_TEXT, $page->html());
    }

    public function testCompanyEmployeeSeeEditButton()
    {
        $companyEmployee = $this->articleByCompanyAuthor->getCompanyAuthor()->getEmployees()->getEmployeesAsUsers()->first();

        $browser = $this->getBrowser()
            ->loginUser($companyEmployee);

        $page = $browser->request(
            'GET',
            sprintf('/articles/view/%s/', $this->articleByCompanyAuthor->getId())
        );

        $this->assertStringContainsString(self::EDIT_BUTTON_TEXT, $page->html());

        $page = $browser->request(
            'GET',
            sprintf('/tidings/view/%s/', $this->tidingsByCompanyAuthor->getId())
        );

        $this->assertStringContainsString(self::EDIT_BUTTON_TEXT, $page->html());

        $page = $browser->request(
            'GET',
            sprintf('/video/view/%s/', $this->videoByCompanyAuthor->getId())
        );

        $this->assertStringContainsString(self::EDIT_BUTTON_TEXT, $page->html());

        $page = $browser->request(
            'GET',
            sprintf('/gallery/view/%s/', $this->galleryByCompanyAuthor->getId())
        );

        $this->assertStringContainsString(self::EDIT_BUTTON_TEXT, $page->html());
    }

    public function testCompanyEmployeeDontSeeDeleteButton()
    {
        $companyEmployee = $this->articleByCompanyAuthor->getCompanyAuthor()->getEmployees()->getEmployeesAsUsers()->first();

        $browser = $this->getBrowser()
            ->loginUser($companyEmployee);

        $page = $browser->request(
            'GET',
            sprintf('/articles/view/%s/', $this->articleByCompanyAuthor->getId())
        );

        $this->assertStringNotContainsString(self::DELETE_BUTTON_TEXT, $page->html());

        $page = $browser->request(
            'GET',
            sprintf('/tidings/view/%s/', $this->tidingsByCompanyAuthor->getId())
        );

        $this->assertStringNotContainsString(self::DELETE_BUTTON_TEXT, $page->html());

        $page = $browser->request(
            'GET',
            sprintf('/video/view/%s/', $this->videoByCompanyAuthor->getId())
        );

        $this->assertStringNotContainsString(self::DELETE_BUTTON_TEXT, $page->html());

        $page = $browser->request(
            'GET',
            sprintf('/gallery/view/%s/', $this->galleryByCompanyAuthor->getId())
        );

        $this->assertStringNotContainsString(self::DELETE_BUTTON_TEXT, $page->html());
    }

    public function testCompanyOwnerSeeEditButton()
    {
        $companyOwner = $this->articleByCompanyAuthor->getCompanyAuthor()->getOwner();

        $browser = $this->getBrowser()
            ->loginUser($companyOwner);

        $page = $browser->request(
            'GET',
            sprintf('/articles/view/%s/', $this->articleByCompanyAuthor->getId())
        );

        $this->assertStringContainsString(self::EDIT_BUTTON_TEXT, $page->html());

        $page = $browser->request(
            'GET',
            sprintf('/tidings/view/%s/', $this->tidingsByCompanyAuthor->getId())
        );

        $this->assertStringContainsString(self::EDIT_BUTTON_TEXT, $page->html());

        $page = $browser->request(
            'GET',
            sprintf('/video/view/%s/', $this->videoByCompanyAuthor->getId())
        );

        $this->assertStringContainsString(self::EDIT_BUTTON_TEXT, $page->html());

        $page = $browser->request(
            'GET',
            sprintf('/gallery/view/%s/', $this->galleryByCompanyAuthor->getId())
        );

        $this->assertStringContainsString(self::EDIT_BUTTON_TEXT, $page->html());
    }

    public function testCompanyOwnerDontSeeDeleteButton()
    {
        $companyOwner = $this->articleByCompanyAuthor->getCompanyAuthor()->getOwner();

        $browser = $this->getBrowser()
            ->loginUser($companyOwner);

        $page = $browser->request(
            'GET',
            sprintf('/articles/view/%s/', $this->articleByCompanyAuthor->getId())
        );

        $this->assertStringNotContainsString(self::DELETE_BUTTON_TEXT, $page->html());

        $page = $browser->request(
            'GET',
            sprintf('/tidings/view/%s/', $this->tidingsByCompanyAuthor->getId())
        );

        $this->assertStringNotContainsString(self::DELETE_BUTTON_TEXT, $page->html());

        $page = $browser->request(
            'GET',
            sprintf('/video/view/%s/', $this->videoByCompanyAuthor->getId())
        );

        $this->assertStringNotContainsString(self::DELETE_BUTTON_TEXT, $page->html());

        $page = $browser->request(
            'GET',
            sprintf('/gallery/view/%s/', $this->galleryByCompanyAuthor->getId())
        );

        $this->assertStringNotContainsString(self::DELETE_BUTTON_TEXT, $page->html());
    }

    public function testAuthorSeeEditButton()
    {
        $articleAuthor = $this->articleByAuthor->getAuthor();

        $browser = $this->getBrowser()
            ->loginUser($articleAuthor);

        $page = $browser->request(
            'GET',
            sprintf('/articles/view/%s/', $this->articleByAuthor->getId())
        );

        $this->assertStringContainsString(self::EDIT_BUTTON_TEXT, $page->html());

        $tidingsAuthor = $this->tidingsByAuthor->getAuthor();

        $browser = $this->getBrowser()
            ->loginUser($tidingsAuthor);

        $page = $browser->request(
            'GET',
            sprintf('/tidings/view/%s/', $this->tidingsByAuthor->getId())
        );

        $this->assertStringContainsString(self::EDIT_BUTTON_TEXT, $page->html());

        $videoAuthor = $this->videoByAuthor->getAuthor();

        $browser = $this->getBrowser()
            ->loginUser($videoAuthor);

        $page = $browser->request(
            'GET',
            sprintf('/video/view/%s/', $this->videoByAuthor->getId())
        );

        $this->assertStringContainsString(self::EDIT_BUTTON_TEXT, $page->html());

        $galleryAuthor = $this->galleryByAuthor->getAuthor();

        $browser = $this->getBrowser()
            ->loginUser($galleryAuthor);

        $page = $browser->request(
            'GET',
            sprintf('/gallery/view/%s/', $this->galleryByAuthor->getId())
        );

        $this->assertStringContainsString(self::EDIT_BUTTON_TEXT, $page->html());
    }

    public function testAuthorDontSeeDeleteButton()
    {
        $author = $this->articleByAuthor->getAuthor();

        $browser = $this->getBrowser()
            ->loginUser($author);

        $page = $browser->request(
            'GET',
            sprintf('/articles/view/%s/', $this->articleByAuthor->getId())
        );

        $this->assertStringNotContainsString(self::DELETE_BUTTON_TEXT, $page->html());

        $tidingsAuthor = $this->tidingsByAuthor->getAuthor();

        $browser = $this->getBrowser()
            ->loginUser($tidingsAuthor);

        $page = $browser->request(
            'GET',
            sprintf('/tidings/view/%s/', $this->tidingsByAuthor->getId())
        );

        $this->assertStringNotContainsString(self::DELETE_BUTTON_TEXT, $page->html());

        $videoAuthor = $this->videoByAuthor->getAuthor();

        $browser = $this->getBrowser()
            ->loginUser($videoAuthor);

        $page = $browser->request(
            'GET',
            sprintf('/video/view/%s/', $this->videoByAuthor->getId())
        );

        $this->assertStringNotContainsString(self::DELETE_BUTTON_TEXT, $page->html());

        $galleryAuthor = $this->galleryByAuthor->getAuthor();

        $browser = $this->getBrowser()
            ->loginUser($galleryAuthor);

        $page = $browser->request(
            'GET',
            sprintf('/gallery/view/%s/', $this->galleryByAuthor->getId())
        );

        $this->assertStringNotContainsString(self::DELETE_BUTTON_TEXT, $page->html());
    }

    public function testUserDontSeeEditAndDeleteButtons()
    {
        $browser = $this->getBrowser()
            ->loginUser($this->user);

        $page = $browser->request(
            'GET',
            sprintf('/articles/view/%s/', $this->articleByAuthor->getId())
        );

        $this->assertStringNotContainsString(self::EDIT_BUTTON_TEXT, $page->html());
        $this->assertStringNotContainsString(self::DELETE_BUTTON_TEXT, $page->html());

        $page = $browser->request(
            'GET',
            sprintf('/tidings/view/%s/', $this->tidingsByAuthor->getId())
        );

        $this->assertStringNotContainsString(self::EDIT_BUTTON_TEXT, $page->html());
        $this->assertStringNotContainsString(self::DELETE_BUTTON_TEXT, $page->html());

        $page = $browser->request(
            'GET',
            sprintf('/video/view/%s/', $this->videoByAuthor->getId())
        );

        $this->assertStringNotContainsString(self::EDIT_BUTTON_TEXT, $page->html());
        $this->assertStringNotContainsString(self::DELETE_BUTTON_TEXT, $page->html());

        $page = $browser->request(
            'GET',
            sprintf('/gallery/view/%s/', $this->galleryByAuthor->getId())
        );

        $this->assertStringNotContainsString(self::EDIT_BUTTON_TEXT, $page->html());
        $this->assertStringNotContainsString(self::DELETE_BUTTON_TEXT, $page->html());
    }

    public function testGuestDontSeeEditAndDeleteButtons()
    {
        $browser = $this->getBrowser();

        $page = $browser->request(
            'GET',
            sprintf('/articles/view/%s/', $this->articleByAuthor->getId())
        );

        $this->assertStringNotContainsString(self::EDIT_BUTTON_TEXT, $page->html());
        $this->assertStringNotContainsString(self::DELETE_BUTTON_TEXT, $page->html());

        $page = $browser->request(
            'GET',
            sprintf('/tidings/view/%s/', $this->tidingsByAuthor->getId())
        );

        $this->assertStringNotContainsString(self::EDIT_BUTTON_TEXT, $page->html());
        $this->assertStringNotContainsString(self::DELETE_BUTTON_TEXT, $page->html());

        $page = $browser->request(
            'GET',
            sprintf('/video/view/%s/', $this->videoByAuthor->getId())
        );

        $this->assertStringNotContainsString(self::EDIT_BUTTON_TEXT, $page->html());
        $this->assertStringNotContainsString(self::DELETE_BUTTON_TEXT, $page->html());

        $page = $browser->request(
            'GET',
            sprintf('/gallery/view/%s/', $this->galleryByAuthor->getId())
        );

        $this->assertStringNotContainsString(self::EDIT_BUTTON_TEXT, $page->html());
        $this->assertStringNotContainsString(self::DELETE_BUTTON_TEXT, $page->html());
    }
}
