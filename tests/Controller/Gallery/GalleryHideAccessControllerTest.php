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

class GalleryHideAccessControllerTest extends TestCase
{
    private const GALLERY_HIDE_MESSAGE = 'Фотография успешно удалена.';

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

    public function testAdminCanHideGalleryByCompanyAuthor()
    {
        $browser = $this->getBrowser()
            ->loginUser($this->admin);

        $browser->request('GET', sprintf('/gallery/%s/hide/', $this->galleryByCompanyAuthor->getId()));

        $page = $browser->followRedirect();

        $this->assertSeeAlertInPageContent('success', self::GALLERY_HIDE_MESSAGE, $page->html());
    }

    public function testModeratorCanHideGalleryByCompanyAuthor()
    {
        $browser = $this->getBrowser()
            ->loginUser($this->moderator);

        $browser->request('GET', sprintf('/gallery/%s/hide/', $this->galleryByCompanyAuthor->getId()));

        $page = $browser->followRedirect();

        $this->assertSeeAlertInPageContent('success', self::GALLERY_HIDE_MESSAGE, $page->html());
    }

    public function testCompanyEmployeeCannotHideGalleryByCompanyAuthor()
    {
        $companyEmployee = $this->galleryByCompanyAuthor->getCompanyAuthor()->getEmployees()->getEmployeesAsUsers()->first();

        $browser = $this->getBrowser()
            ->loginUser($companyEmployee);

        $browser->request('GET', sprintf('/gallery/%s/hide/', $this->galleryByCompanyAuthor->getId()));

        $this->assertEquals(Response::HTTP_FORBIDDEN, $this->getBrowser()->getResponse()->getStatusCode());
    }

    public function testCompanyOwnerCannotHideGalleryByCompanyAuthor()
    {
        $companyOwner = $this->galleryByCompanyAuthor->getCompanyAuthor()->getOwner();

        $browser = $this->getBrowser()
            ->loginUser($companyOwner);

        $browser->request('GET', sprintf('/gallery/%s/hide/', $this->galleryByCompanyAuthor->getId()));

        $this->assertEquals(Response::HTTP_FORBIDDEN, $this->getBrowser()->getResponse()->getStatusCode());
    }

    public function testAuthorCannotHideGallery()
    {
        $author = $this->galleryByAuthor->getAuthor();

        $browser = $this->getBrowser()
            ->loginUser($author);

        $browser->request('GET', sprintf('/gallery/%s/hide/', $this->galleryByAuthor->getId()));

        $this->assertEquals(Response::HTTP_FORBIDDEN, $this->getBrowser()->getResponse()->getStatusCode());
    }

    public function testUserCannotHideGallery()
    {
        $browser = $this->getBrowser()
            ->loginUser($this->user);

        $browser->request('GET', sprintf('/gallery/%s/hide/', $this->galleryByAuthor->getId()));

        $this->assertEquals(Response::HTTP_FORBIDDEN, $this->getBrowser()->getResponse()->getStatusCode());
    }

    public function testGuestCannotHideGallery()
    {
        $browser = $this->getBrowser();

        $browser->request('GET', sprintf('/gallery/%s/hide/', $this->galleryByAuthor->getId()));

        $this->assertEquals(Response::HTTP_FOUND, $this->getBrowser()->getResponse()->getStatusCode());
    }
}
