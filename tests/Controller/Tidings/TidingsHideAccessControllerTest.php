<?php

namespace Tests\Controller\Tidings;

use App\Domain\Record\Tidings\Entity\Tidings;
use App\Domain\User\Entity\User;
use Symfony\Component\HttpFoundation\Response;
use Tests\Controller\TestCase;
use Tests\DataFixtures\ORM\Record\Tidings\LoadSimpleTidings;
use Tests\DataFixtures\ORM\Record\Tidings\LoadTidingsByCompanyAuthor;
use Tests\DataFixtures\ORM\User\LoadAdminUser;
use Tests\DataFixtures\ORM\User\LoadModeratorUser;
use Tests\DataFixtures\ORM\User\LoadUserWithoutRecords;

class TidingsHideAccessControllerTest extends TestCase
{
    private const TIDINGS_HIDE_MESSAGE = 'Запись успешно удалена.';

    private Tidings $tidingsByCompanyAuthor;
    private Tidings $tidingsByAuthor;
    private User $admin;
    private User $moderator;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $referenceRepository = $this->loadFixtures([
            LoadTidingsByCompanyAuthor::class,
            LoadSimpleTidings::class,
            LoadAdminUser::class,
            LoadModeratorUser::class,
            LoadUserWithoutRecords::class,
        ])->getReferenceRepository();

        $tidingsByCompanyAuthor = $referenceRepository->getReference(LoadTidingsByCompanyAuthor::REFERENCE_NAME);
        assert($tidingsByCompanyAuthor instanceof Tidings);
        $this->tidingsByCompanyAuthor = $tidingsByCompanyAuthor;

        $tidingsByAuthor = $referenceRepository->getReference(LoadSimpleTidings::REFERENCE_NAME);
        assert($tidingsByAuthor instanceof Tidings);
        $this->tidingsByAuthor = $tidingsByAuthor;

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

        $browser->request('GET', sprintf('/tidings/delete/%s/', $this->tidingsByCompanyAuthor->getId()));

        $this->assertTrue($browser->getResponse()->isRedirect('/tidings/'));

        $page = $browser->followRedirect();

        $this->assertSeeAlertInPageContent('success', self::TIDINGS_HIDE_MESSAGE, $page->html());
    }

    public function testModeratorCanHideArticleByCompanyAuthor()
    {
        $browser = $this->getBrowser()
            ->loginUser($this->moderator);

        $browser->request('GET', sprintf('/tidings/delete/%s/', $this->tidingsByCompanyAuthor->getId()));

        $this->assertTrue($browser->getResponse()->isRedirect('/tidings/'));

        $page = $browser->followRedirect();

        $this->assertSeeAlertInPageContent('success', self::TIDINGS_HIDE_MESSAGE, $page->html());
    }

    public function testCompanyEmployeeCannotHideArticleByCompanyAuthor()
    {
        $companyEmployee = $this->tidingsByCompanyAuthor->getCompanyAuthor()->getEmployees()->getEmployeesAsUsers()->first();

        $browser = $this->getBrowser()
            ->loginUser($companyEmployee);

        $browser->request('GET', sprintf('/tidings/delete/%s/', $this->tidingsByCompanyAuthor->getId()));

        $this->assertEquals(Response::HTTP_FORBIDDEN, $this->getBrowser()->getResponse()->getStatusCode());
    }

    public function testCompanyOwnerCannotHideArticleByCompanyAuthor()
    {
        $companyOwner = $this->tidingsByCompanyAuthor->getCompanyAuthor()->getOwner();

        $browser = $this->getBrowser()
            ->loginUser($companyOwner);

        $browser->request('GET', sprintf('/tidings/delete/%s/', $this->tidingsByCompanyAuthor->getId()));

        $this->assertEquals(Response::HTTP_FORBIDDEN, $this->getBrowser()->getResponse()->getStatusCode());
    }

    public function testAuthorCannotHideArticle()
    {
        $author = $this->tidingsByAuthor->getAuthor();

        $browser = $this->getBrowser()
            ->loginUser($author);

        $browser->request('GET', sprintf('/tidings/delete/%s/', $this->tidingsByAuthor->getId()));

        $this->assertEquals(Response::HTTP_FORBIDDEN, $this->getBrowser()->getResponse()->getStatusCode());
    }

    public function testUserCannotHideArticle()
    {
        $browser = $this->getBrowser()
            ->loginUser($this->user);

        $browser->request('GET', sprintf('/tidings/delete/%s/', $this->tidingsByAuthor->getId()));

        $this->assertEquals(Response::HTTP_FORBIDDEN, $this->getBrowser()->getResponse()->getStatusCode());
    }

    public function testGuestCannotHideArticle()
    {
        $browser = $this->getBrowser();

        $browser->request('GET', sprintf('/tidings/delete/%s/', $this->tidingsByAuthor->getId()));

        $this->assertEquals(Response::HTTP_FOUND, $this->getBrowser()->getResponse()->getStatusCode());
    }
}
