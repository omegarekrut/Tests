<?php

namespace Tests\Unit\Twig\User;

use App\Domain\User\Entity\User;
use App\Domain\User\Entity\ValueObject\FishingInformation as FishingInformationObject;
use App\Twig\User\FishingInformation;
use Tests\Unit\TestCase;

class FishingInformationTest extends TestCase
{
    private FishingInformation $filter;

    protected function setUp(): void
    {
        parent::setUp();

        $this->filter = new FishingInformation();
    }

    protected function tearDown(): void
    {
        unset($this->filter);

        parent::tearDown();
    }

    public function testFilterUserWithoutFishingInformation(): void
    {
        $emptyFishingInformation = $this->createConfiguredMock(FishingInformationObject::class, [
            'isNotEmpty' => false,
        ]);

        $userWithFishingInformation = $this->createConfiguredMock(User::class, [
            'getFishingInformation' => $emptyFishingInformation,
        ]);

        $this->assertFalse(($this->filter)($userWithFishingInformation));
    }

    public function testFilterUserWithFishingInformation(): void
    {
        $fishingInformation = $this->createConfiguredMock(FishingInformationObject::class, [
            'isNotEmpty' => true,
        ]);

        $userWithFishingInformation = $this->createConfiguredMock(User::class, [
            'getFishingInformation' => $fishingInformation,
        ]);

        $this->assertTrue(($this->filter)($userWithFishingInformation));
    }
}
