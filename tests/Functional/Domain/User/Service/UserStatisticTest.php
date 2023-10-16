<?php

namespace Tests\Functional\Domain\User\Service;

use App\Domain\User\Service\UserStatistic;
use Tests\DataFixtures\ORM\User\LoadNumberedUsers;
use Tests\DataFixtures\ORM\User\LoadUnsubscribedUserOnWeeklyNews;
use Tests\DataFixtures\ORM\User\LoadUserWithBouncedEmail;
use Tests\Functional\TestCase;

class UserStatisticTest extends TestCase
{
    private $userStatistic;

    protected function setUp(): void
    {
        parent::setUp();

        $this->loadFixtures([
            LoadNumberedUsers::class,
            LoadUnsubscribedUserOnWeeklyNews::class,
            LoadUserWithBouncedEmail::class,
        ]);

        $this->userStatistic = $this->getContainer()->get(UserStatistic::class);
    }

    protected function tearDown(): void
    {
        unset($this->userStatistic);

        parent::tearDown();
    }

    public function testQuantityOfUsers(): void
    {
        $this->assertGreaterThan(0, $this->userStatistic->getQuantityOfUsers());
    }

    public function testQuantityOfUnsubscribed(): void
    {
        $this->assertGreaterThan(0, $this->userStatistic->getQuantityOfUnsubscribed());
    }

    public function testQuantityOfInvalidEmail(): void
    {
        $this->assertGreaterThan(0, $this->userStatistic->getQuantityOfBouncedEmail());
    }

    public function testQuantityOfSubscribed(): void
    {
        $this->assertGreaterThan(0, $this->userStatistic->getQuantityOfSubscribed());
    }
}
