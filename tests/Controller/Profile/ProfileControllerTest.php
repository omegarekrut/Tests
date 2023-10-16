<?php

namespace Tests\Controller\Profile;

use App\Domain\Record\Tidings\Entity\Tidings;
use App\Domain\User\Entity\User;
use Symfony\Component\HttpFoundation\Response;
use Tests\Controller\TestCase;
use Tests\DataFixtures\ORM\Company\Company\LoadCompanyWithFixedCoordinates;
use Tests\DataFixtures\ORM\Company\Company\LoadCompanyWithOwner;
use Tests\DataFixtures\ORM\Record\Tidings\LoadTidingsWithComment;
use Tests\DataFixtures\ORM\User\LoadTestUser;
use Tests\DataFixtures\ORM\User\LoadUserWithComments;

class ProfileControllerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->clearDatabase();

        $this->browser = $this->getBrowser();
    }

    public function testSeeMainProfilePage(): void
    {
        $user = $this->loadFixture(LoadTestUser::class, User::class);

        $this->browser->loginUser($user);

        $this->amOnProfilePage($user);

        $this->assertEquals(Response::HTTP_OK, $this->browser->getResponse()->getStatusCode());
    }

    public function testSeeProfilePageSelfAdsSidebar(): void
    {
        $user = $this->loadFixture(LoadTestUser::class, User::class);

        $this->browser->loginUser($user);

        $this->amOnProfilePage($user);

        $this->assertStringContainsString('Мои объявления', $this->browser->getResponse()->getContent());
    }

    public function testSeeComments(): void
    {
        $user = $this->loadFixture(LoadUserWithComments::class, User::class);

        $this->browser->loginUser($user);

        $this->amOnProfilePage($user);

        $this->assertStringContainsString('Мои объявления', $this->browser->getResponse()->getContent());
    }

    public function testSeeTidings(): void
    {
        $this->loadFixture(LoadTidingsWithComment::class, Tidings::class);
        $user = $this->loadFixture(LoadTestUser::class, User::class);

        $this->browser->loginUser($user);

        $this->amOnProfilePage($user);

        $this->assertStringContainsString('Мои объявления', $this->browser->getResponse()->getContent());
    }

    public function testSeeArticles(): void
    {
        $user = $this->loadFixture(LoadTestUser::class, User::class);

        $this->browser->loginUser($user);

        $this->amOnProfilePage($user);

        $this->assertStringContainsString('Мои объявления', $this->browser->getResponse()->getContent());
    }

    public function testSeeGallery(): void
    {
        $user = $this->loadFixture(LoadTestUser::class, User::class);

        $this->browser->loginUser($user);

        $this->amOnProfilePage($user);

        $this->assertStringContainsString('Мои объявления', $this->browser->getResponse()->getContent());
    }

    public function testSeeVideo(): void
    {
        $user = $this->loadFixture(LoadTestUser::class, User::class);

        $this->browser->loginUser($user);

        $this->amOnProfilePage($user);

        $this->assertStringContainsString('Мои объявления', $this->browser->getResponse()->getContent());
    }

    public function testTackleReviews(): void
    {
        $user = $this->loadFixture(LoadTestUser::class, User::class);

        $this->browser->loginUser($user);

        $this->amOnProfilePage($user);

        $this->assertStringContainsString('Мои объявления', $this->browser->getResponse()->getContent());
    }

    public function testMapPoints(): void
    {
        $user = $this->loadFixture(LoadTestUser::class, User::class);

        $this->browser->loginUser($user);

        $this->amOnProfilePage($user);

        $this->assertStringContainsString('Мои объявления', $this->browser->getResponse()->getContent());
    }

    public function testSeeCompanies()
    {
        $referenceRepository = $this->loadFixtures([
            LoadTestUser::class,
            LoadCompanyWithOwner::class,
            LoadCompanyWithFixedCoordinates::class,
        ])->getReferenceRepository();

        $user = $referenceRepository
            ->getReference(LoadTestUser::USER_TEST);
        assert($user instanceof User);

        $url = sprintf('/users/profile/%d/', $user->getId());

        $client = $this->getBrowser()->loginUser($user);

        $page = $client->request('GET', $url);

        $this->assertEquals(Response::HTTP_OK, $this->getBrowser()->getResponse()->getStatusCode());
        $this->assertStringContainsString('company-with-owner', $page->text());
        $this->assertStringContainsString('company-with-fixed-coordinates', $page->text());
    }

    private function amOnProfilePage(User $user): void
    {
        $this->browser->request('GET', sprintf('/users/profile/%d/%s', $user->getId(), ''));
    }
}
