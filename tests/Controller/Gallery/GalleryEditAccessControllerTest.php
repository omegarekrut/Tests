<?php

namespace Tests\Controller\Gallery;

use App\Domain\Record\Gallery\Entity\Gallery;
use App\Domain\User\Entity\User;
use Symfony\Component\HttpFoundation\Response;
use Tests\Controller\TestCase;
use Tests\DataFixtures\ORM\Record\Gallery\LoadGalleryByCompanyAuthor;
use Tests\DataFixtures\ORM\Record\LoadGalleryWithOwner;
use Tests\DataFixtures\ORM\User\LoadAdminUser;
use Tests\DataFixtures\ORM\User\LoadModeratorUser;
use Tests\DataFixtures\ORM\User\LoadUserWithoutRecords;

class GalleryEditAccessControllerTest extends TestCase
{
    private const GALLERY_EDIT_TITLE = 'Редактировать фото';

    private Gallery $galleryByCompanyAuthor;
    private Gallery $galleryByAuthor;
    private User $admin;
    private User $moderator;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $referenceRepository = $this->loadFixtures([
            LoadGalleryByCompanyAuthor::class,
            LoadGalleryWithOwner::class,
            LoadAdminUser::class,
            LoadModeratorUser::class,
            LoadUserWithoutRecords::class,
        ])->getReferenceRepository();

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

    public function testCompanyEmployeeCanEditGalleryByCompanyAuthor()
    {
        $companyEmployee = $this->galleryByCompanyAuthor->getCompanyAuthor()->getEmployees()->getEmployeesAsUsers()->first();

        $browser = $this->getBrowser()
            ->loginUser($companyEmployee);

        $page = $browser->request('GET', sprintf('/gallery/%s/edit/', $this->galleryByCompanyAuthor->getId()));

        $this->assertEquals(Response::HTTP_OK, $this->getBrowser()->getResponse()->getStatusCode());
        $this->assertStringContainsString(self::GALLERY_EDIT_TITLE, $page->text());
    }

    public function testCompanyOwnerCanEditGalleryByCompanyAuthor()
    {
        $companyOwner = $this->galleryByCompanyAuthor->getCompanyAuthor()->getOwner();

        $browser = $this->getBrowser()
            ->loginUser($companyOwner);

        $page = $browser->request('GET', sprintf('/gallery/%s/edit/', $this->galleryByCompanyAuthor->getId()));

        $this->assertEquals(Response::HTTP_OK, $this->getBrowser()->getResponse()->getStatusCode());
        $this->assertStringContainsString(self::GALLERY_EDIT_TITLE, $page->text());
    }

    public function testAdminCanEditGalleryByCompanyAuthor()
    {
        $browser = $this->getBrowser()
            ->loginUser($this->admin);

        $page = $browser->request('GET', sprintf('/gallery/%s/edit/', $this->galleryByCompanyAuthor->getId()));

        $this->assertEquals(Response::HTTP_OK, $this->getBrowser()->getResponse()->getStatusCode());
        $this->assertStringContainsString(self::GALLERY_EDIT_TITLE, $page->text());
    }

    public function testModeratorCanEditGalleryByCompanyAuthor()
    {
        $browser = $this->getBrowser()
            ->loginUser($this->moderator);

        $page = $browser->request('GET', sprintf('/gallery/%s/edit/', $this->galleryByCompanyAuthor->getId()));

        $this->assertEquals(Response::HTTP_OK, $this->getBrowser()->getResponse()->getStatusCode());
        $this->assertStringContainsString(self::GALLERY_EDIT_TITLE, $page->text());
    }

    public function testAuthorCanEditGallery()
    {
        $author = $this->galleryByAuthor->getAuthor();

        $browser = $this->getBrowser()
            ->loginUser($author);

        $page = $browser->request('GET', sprintf('/gallery/%s/edit/', $this->galleryByAuthor->getId()));

        $this->assertEquals(Response::HTTP_OK, $this->getBrowser()->getResponse()->getStatusCode());
        $this->assertStringContainsString(self::GALLERY_EDIT_TITLE, $page->text());
    }

    public function testUserCannotEditArticle()
    {
        $browser = $this->getBrowser()
            ->loginUser($this->user);

        $browser->request('GET', sprintf('/gallery/%s/edit/', $this->galleryByAuthor->getId()));

        $this->assertEquals(Response::HTTP_FORBIDDEN, $this->getBrowser()->getResponse()->getStatusCode());
    }

    public function testGuestCannotEditGallery()
    {
        $browser = $this->getBrowser();

        $browser->request('GET', sprintf('/gallery/%s/edit/', $this->galleryByAuthor->getId()));

        $this->assertEquals(Response::HTTP_FOUND, $this->getBrowser()->getResponse()->getStatusCode());
    }
}
