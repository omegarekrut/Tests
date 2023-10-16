<?php

namespace Tests\Controller\Tackle;

use App\Domain\Record\Tackle\Entity\Tackle;
use App\Domain\User\Entity\User;
use Symfony\Component\HttpFoundation\Response;
use Tests\Controller\TestCase;
use Tests\DataFixtures\ORM\LoadCategories;
use Tests\DataFixtures\ORM\Record\LoadTackleWithoutReview;
use Tests\DataFixtures\ORM\User\LoadAdminUser;
use Tests\DataFixtures\ORM\User\LoadModeratorUser;
use Tests\DataFixtures\ORM\User\LoadMostActiveUser;

class TackleAccessControllerTest extends TestCase
{
    private const TACKLE_TITLE = 'Отзывы о снастях';
    private const TACKLE_OVERALL_RATING = 'средняя оценка';

    private User $admin;
    private User $moderator;
    private User $user;
    private Tackle $tackle;

    protected function setUp(): void
    {
        parent::setUp();

        $referenceRepository = $this->loadFixtures([
            LoadAdminUser::class,
            LoadModeratorUser::class,
            LoadMostActiveUser::class,
            LoadCategories::class,
            LoadTackleWithoutReview::class,
        ])->getReferenceRepository();

        $admin = $referenceRepository->getReference(LoadAdminUser::REFERENCE_NAME);
        assert($admin instanceof User);

        $moderator = $referenceRepository->getReference(LoadModeratorUser::REFERENCE_NAME);
        assert($moderator instanceof User);

        $user = $referenceRepository->getReference(LoadMostActiveUser::USER_MOST_ACTIVE);
        assert($user instanceof User);

        $tackle = $referenceRepository->getReference(LoadTackleWithoutReview::getRandReferenceName());
        assert($tackle instanceof Tackle);

        $this->admin = $admin;
        $this->moderator = $moderator;
        $this->user = $user;
        $this->tackle = $tackle;
    }

    public function testAllowOnIndexPageForAdministrator(): void
    {
        $client = $this->getBrowser()->loginUser($this->admin);
        $url = '/tackles/';

        $page = $client->request('GET', $url);

        $this->assertEquals(Response::HTTP_OK, $this->getBrowser()->getResponse()->getStatusCode());

        $this->assertStringContainsString(
            self::TACKLE_TITLE,
            $page->html()
        );
    }

    public function testAllowOnIndexPageForModerator(): void
    {
        $client = $this->getBrowser()->loginUser($this->moderator);
        $url = '/tackles/';

        $page = $client->request('GET', $url);

        $this->assertEquals(Response::HTTP_OK, $this->getBrowser()->getResponse()->getStatusCode());

        $this->assertStringContainsString(
            self::TACKLE_TITLE,
            $page->html()
        );
    }

    public function testAllowOnIndexPageForAuthorizedUser(): void
    {
        $client = $this->getBrowser()->loginUser($this->user);
        $url = '/tackles/';

        $page = $client->request('GET', $url);

        $this->assertEquals(Response::HTTP_OK, $this->getBrowser()->getResponse()->getStatusCode());

        $this->assertStringContainsString(
            self::TACKLE_TITLE,
            $page->html()
        );
    }

    public function testAllowOnIndexPageForGuest(): void
    {
        $client = $this->getBrowser();
        $url = '/tackles/';

        $page = $client->request('GET', $url);

        $this->assertEquals(Response::HTTP_OK, $this->getBrowser()->getResponse()->getStatusCode());

        $this->assertStringContainsString(
            self::TACKLE_TITLE,
            $page->html()
        );
    }

    public function testAllowAccessOnTacklePageForAdministrator(): void
    {
        $client = $this->getBrowser()->loginUser($this->admin);
        $url = sprintf('/tackles/view/%s/', $this->tackle->getId());

        $page = $client->request('GET', $url);

        $this->assertEquals(Response::HTTP_OK, $this->getBrowser()->getResponse()->getStatusCode());

        $this->assertStringContainsString(
            self::TACKLE_OVERALL_RATING,
            $page->html()
        );
    }

    public function testAllowAccessOnTacklePageForModerator(): void
    {
        $client = $this->getBrowser()->loginUser($this->moderator);
        $url = sprintf('/tackles/view/%s/', $this->tackle->getId());

        $page = $client->request('GET', $url);

        $this->assertEquals(Response::HTTP_OK, $this->getBrowser()->getResponse()->getStatusCode());

        $this->assertStringContainsString(
            self::TACKLE_OVERALL_RATING,
            $page->html()
        );
    }

    public function testAllowAccessOnTacklePageForAuthorizedUser(): void
    {
        $client = $this->getBrowser()->loginUser($this->user);
        $url = sprintf('/tackles/view/%s/', $this->tackle->getId());

        $page = $client->request('GET', $url);

        $this->assertEquals(Response::HTTP_OK, $this->getBrowser()->getResponse()->getStatusCode());

        $this->assertStringContainsString(
            self::TACKLE_OVERALL_RATING,
            $page->html()
        );
    }

    public function testAllowAccessOnTacklePageForGuest(): void
    {
        $client = $this->getBrowser();
        $url = sprintf('/tackles/view/%s/', $this->tackle->getId());

        $page = $client->request('GET', $url);

        $this->assertEquals(Response::HTTP_OK, $this->getBrowser()->getResponse()->getStatusCode());

        $this->assertStringContainsString(
            self::TACKLE_OVERALL_RATING,
            $page->html()
        );
    }
}
