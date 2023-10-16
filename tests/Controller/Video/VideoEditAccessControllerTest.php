<?php

namespace Tests\Controller\Video;

use App\Domain\Record\Video\Entity\Video;
use App\Domain\User\Entity\User;
use Symfony\Component\HttpFoundation\Response;
use Tests\Controller\TestCase;
use Tests\DataFixtures\ORM\Record\Video\LoadSimpleVideo;
use Tests\DataFixtures\ORM\Record\Video\LoadVideoByCompanyAuthor;
use Tests\DataFixtures\ORM\User\LoadAdminUser;
use Tests\DataFixtures\ORM\User\LoadModeratorUser;
use Tests\DataFixtures\ORM\User\LoadUserWithoutRecords;

class VideoEditAccessControllerTest extends TestCase
{
    private const VIDEO_EDIT_TITLE = 'Редактировать видео';

    private Video $videoByCompanyAuthor;
    private Video $videoByAuthor;
    private User $admin;
    private User $moderator;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $referenceRepository = $this->loadFixtures([
            LoadVideoByCompanyAuthor::class,
            LoadSimpleVideo::class,
            LoadAdminUser::class,
            LoadModeratorUser::class,
            LoadUserWithoutRecords::class,
        ])->getReferenceRepository();

        $videoByCompanyAuthor = $referenceRepository->getReference(LoadVideoByCompanyAuthor::REFERENCE_NAME);
        assert($videoByCompanyAuthor instanceof Video);
        $this->videoByCompanyAuthor = $videoByCompanyAuthor;

        $videoByAuthor = $referenceRepository->getReference(LoadSimpleVideo::REFERENCE_NAME);
        assert($videoByAuthor instanceof Video);
        $this->videoByAuthor = $videoByAuthor;

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

    public function testCompanyEmployeeCanEditArticleByCompanyAuthor()
    {
        $companyEmployee = $this->videoByCompanyAuthor->getCompanyAuthor()->getEmployees()->getEmployeesAsUsers()->first();

        $browser = $this->getBrowser()
            ->loginUser($companyEmployee);

        $page = $browser->request('GET', sprintf('/video/edit/%s/', $this->videoByCompanyAuthor->getId()));

        $this->assertEquals(Response::HTTP_OK, $this->getBrowser()->getResponse()->getStatusCode());
        $this->assertStringContainsString(self::VIDEO_EDIT_TITLE, $page->text());
    }

    public function testCompanyOwnerCanEditArticleByCompanyAuthor()
    {
        $companyOwner = $this->videoByCompanyAuthor->getCompanyAuthor()->getOwner();

        $browser = $this->getBrowser()
            ->loginUser($companyOwner);

        $page = $browser->request('GET', sprintf('/video/edit/%s/', $this->videoByCompanyAuthor->getId()));

        $this->assertEquals(Response::HTTP_OK, $this->getBrowser()->getResponse()->getStatusCode());
        $this->assertStringContainsString(self::VIDEO_EDIT_TITLE, $page->text());
    }

    public function testAdminCanEditArticleByCompanyAuthor()
    {
        $browser = $this->getBrowser()
            ->loginUser($this->admin);

        $page = $browser->request('GET', sprintf('/video/edit/%s/', $this->videoByCompanyAuthor->getId()));

        $this->assertEquals(Response::HTTP_OK, $this->getBrowser()->getResponse()->getStatusCode());
        $this->assertStringContainsString(self::VIDEO_EDIT_TITLE, $page->text());
    }

    public function testModeratorCanEditArticleByCompanyAuthor()
    {
        $browser = $this->getBrowser()
            ->loginUser($this->moderator);

        $page = $browser->request('GET', sprintf('/video/edit/%s/', $this->videoByCompanyAuthor->getId()));

        $this->assertEquals(Response::HTTP_OK, $this->getBrowser()->getResponse()->getStatusCode());
        $this->assertStringContainsString(self::VIDEO_EDIT_TITLE, $page->text());
    }

    public function testAuthorCanEditArticle()
    {
        $author = $this->videoByAuthor->getAuthor();

        $browser = $this->getBrowser()
            ->loginUser($author);

        $page = $browser->request('GET', sprintf('/video/edit/%s/', $this->videoByAuthor->getId()));

        $this->assertEquals(Response::HTTP_OK, $this->getBrowser()->getResponse()->getStatusCode());
        $this->assertStringContainsString(self::VIDEO_EDIT_TITLE, $page->text());
    }

    public function testUserCannotEditArticle()
    {
        $browser = $this->getBrowser()
            ->loginUser($this->user);

        $browser->request('GET', sprintf('/video/edit/%s/', $this->videoByAuthor->getId()));

        $this->assertEquals(Response::HTTP_FORBIDDEN, $this->getBrowser()->getResponse()->getStatusCode());
    }

    public function testGuestCannotEditArticle()
    {
        $browser = $this->getBrowser();

        $browser->request('GET', sprintf('/video/edit/%s/', $this->videoByAuthor->getId()));

        $this->assertEquals(Response::HTTP_FOUND, $this->getBrowser()->getResponse()->getStatusCode());
    }
}
