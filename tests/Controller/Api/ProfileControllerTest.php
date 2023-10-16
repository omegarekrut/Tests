<?php

namespace Tests\Controller\Api;

use App\Domain\User\Entity\User;
use Symfony\Component\HttpFoundation\Response;
use Tests\Controller\TestCase;
use Tests\DataFixtures\ORM\User\LoadTestUser;

class ProfileControllerTest extends TestCase
{
    public function testAccessToMeProfileAsGuest(): void
    {
        $browser = $this->getBrowser();

        $browser->xmlHttpRequest('GET', '/api/profile/me/');

        $this->assertEquals(Response::HTTP_FORBIDDEN, $this->getBrowser()->getResponse()->getStatusCode());
    }

    public function testAccessToMeProfileAsUser(): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadTestUser::class,
        ])->getReferenceRepository();

        $testUser = $referenceRepository->getReference(LoadTestUser::USER_TEST);
        assert($testUser instanceof User);

        $browser = $this->getBrowser();
        $browser->loginUser($testUser);

        $browser->xmlHttpRequest('GET', '/api/profile/me/');

        $this->assertEquals(Response::HTTP_OK, $this->getBrowser()->getResponse()->getStatusCode());

        $response = json_decode($this->getBrowser()->getResponse()->getContent(), true);

        $this->assertCount(4, $response);
        $this->assertArrayHasKey('id', $response);
        $this->assertArrayHasKey('totalCountNotification', $response);
        $this->assertArrayHasKey('countNotification', $response);
        $this->assertArrayHasKey('countUnreadPrivateMessages', $response);
    }
}
