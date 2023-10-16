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

class VideoHideAccessControllerTest extends TestCase
{
    private const VIDEO_HIDE_MESSAGE = 'Видео успешно удалено.';

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

    public function testAdminCanHideArticleByCompanyAuthor()
    {
        $browser = $this->getBrowser()
            ->loginUser($this->admin);

        $browser->request('GET', sprintf('/video/hide/%s/', $this->videoByCompanyAuthor->getId()));

        $this->assertTrue($browser->getResponse()->isRedirect('/video/'));

        $page = $browser->followRedirect();

        $this->assertSeeAlertInPageContent('success', self::VIDEO_HIDE_MESSAGE, $page->html());
    }

    public function testModeratorCanHideArticleByCompanyAuthor()
    {
        $browser = $this->getBrowser()
            ->loginUser($this->moderator);

        $browser->request('GET', sprintf('/video/hide/%s/', $this->videoByCompanyAuthor->getId()));

        $this->assertTrue($browser->getResponse()->isRedirect('/video/'));

        $page = $browser->followRedirect();

        $this->assertSeeAlertInPageContent('success', self::VIDEO_HIDE_MESSAGE, $page->html());
    }

    public function testCompanyEmployeeCannotHideArticleByCompanyAuthor()
    {
        $companyEmployee = $this->videoByCompanyAuthor->getCompanyAuthor()->getEmployees()->getEmployeesAsUsers()->first();

        $browser = $this->getBrowser()
            ->loginUser($companyEmployee);

        $browser->request('GET', sprintf('/video/hide/%s/', $this->videoByCompanyAuthor->getId()));

        $this->assertEquals(Response::HTTP_FORBIDDEN, $this->getBrowser()->getResponse()->getStatusCode());
    }

    public function testCompanyOwnerCannotHideArticleByCompanyAuthor()
    {
        $companyOwner = $this->videoByCompanyAuthor->getCompanyAuthor()->getOwner();

        $browser = $this->getBrowser()
            ->loginUser($companyOwner);

        $browser->request('GET', sprintf('/video/hide/%s/', $this->videoByCompanyAuthor->getId()));

        $this->assertEquals(Response::HTTP_FORBIDDEN, $this->getBrowser()->getResponse()->getStatusCode());
    }

    public function testAuthorCannotHideArticle()
    {
        $author = $this->videoByAuthor->getAuthor();

        $browser = $this->getBrowser()
            ->loginUser($author);

        $browser->request('GET', sprintf('/video/hide/%s/', $this->videoByAuthor->getId()));

        $this->assertEquals(Response::HTTP_FORBIDDEN, $this->getBrowser()->getResponse()->getStatusCode());
    }

    public function testUserCannotHideArticle()
    {
        $browser = $this->getBrowser()
            ->loginUser($this->user);

        $browser->request('GET', sprintf('/video/hide/%s/', $this->videoByAuthor->getId()));

        $this->assertEquals(Response::HTTP_FORBIDDEN, $this->getBrowser()->getResponse()->getStatusCode());
    }

    public function testGuestCannotHideArticle()
    {
        $browser = $this->getBrowser();

        $browser->request('GET', sprintf('/video/hide/%s/', $this->videoByAuthor->getId()));

        $this->assertEquals(Response::HTTP_FOUND, $this->getBrowser()->getResponse()->getStatusCode());
    }
}
