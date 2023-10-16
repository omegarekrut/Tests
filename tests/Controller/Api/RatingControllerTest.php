<?php

namespace Tests\Controller\Api;

use App\Domain\User\Entity\User;
use Symfony\Component\HttpFoundation\Response;
use Tests\Controller\TestCase;
use Tests\DataFixtures\ORM\User\LoadTestUser;

class RatingControllerTest extends TestCase
{
    private User $testUser;

    protected function setUp(): void
    {
        parent::setUp();

        $referenceRepository = $this->loadFixtures([
            LoadTestUser::class,
        ])->getReferenceRepository();

        $testUser = $referenceRepository->getReference(LoadTestUser::USER_TEST);
        assert($testUser instanceof User);

        $this->testUser = $testUser;
    }

    public function testGetTopUsers(): void
    {
        $browser = $this->getBrowser();

        $browser->request('GET', '/api/users/rating/top/');

        $this->assertRatingTopResponse();
    }

    public function testGetTopYearUsers(): void
    {
        $browser = $this->getBrowser();

        $browser->request('GET', '/api/users/rating/top-year/');

        $this->assertRatingTopResponse();
    }

    private function assertRatingTopResponse(): void
    {
        $this->assertEquals(Response::HTTP_OK, $this->getBrowser()->getResponse()->getStatusCode());

        $response = json_decode($this->getBrowser()->getResponse()->getContent(), true);

        $this->assertCount(2, $response);
        $this->assertArrayHasKey('linkToList', $response);
        $this->assertArrayHasKey('users', $response);

        $this->assertNotEmpty($response['linkToList']);
        $this->assertCount(1, $response['users']);

        $userData = array_shift($response['users']);

        $this->assertUserData($userData);
    }

    /**
     * @param mixed[] $userData
     */
    private function assertUserData(array $userData): void
    {
        $this->assertCount(12, $userData);

        $this->assertArrayHasKey('id', $userData);
        $this->assertArrayHasKey('type', $userData);
        $this->assertArrayHasKey('name', $userData);

        $this->assertArrayHasKey('avatar', $userData);
        $this->assertArrayHasKey('withOriginalSide', $userData['avatar']);
        $this->assertArrayHasKey('withSmallSide', $userData['avatar']);
        $this->assertArrayHasKey('withSmallSideForMail', $userData['avatar']);

        $this->assertArrayHasKey('subscribers', $userData);
        $this->assertArrayHasKey('company', $userData);
        $this->assertArrayHasKey('city', $userData);
        $this->assertArrayHasKey('globalRating', $userData);
        $this->assertArrayHasKey('activityRating', $userData);
        $this->assertArrayHasKey('subscribeUrl', $userData);
        $this->assertArrayHasKey('unsubscribeUrl', $userData);
        $this->assertArrayHasKey('profileUrl', $userData);
    }
}
