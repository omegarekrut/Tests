<?php

namespace Tests\Unit\Twig\User;

use App\Domain\User\Entity\User;
use App\Domain\User\Entity\ValueObject\City;
use App\Domain\User\Entity\ValueObject\Gender;
use App\Twig\User\BriefInformation;
use Carbon\Carbon;
use DateTimeInterface;
use Generator;
use Tests\Unit\TestCase;

/**
 * @group twig
 */
class BriefInformationTest extends TestCase
{
    private BriefInformation $filter;

    protected function setUp(): void
    {
        parent::setUp();

        $this->filter = new BriefInformation();
    }

    protected function tearDown(): void
    {
        unset($this->filter);

        parent::tearDown();
    }
    /**
     * @dataProvider usersWithBriefInformation
     */
    public function testFilter(?string $city, ?DateTimeInterface $birthdate, string $gender, bool $expectedStatus): void
    {
        $user = $this->createConfiguredMock(User::class, [
            'getCity' => is_null($city) ? $city : $this->createCityWithName($city),
            'getBirthdate' => $birthdate,
            'getGender' => $gender,
        ]);

        $this->assertEquals($expectedStatus, ($this->filter)($user));
    }

    public function usersWithBriefInformation(): Generator
    {
        yield [
            'city' => null,
            'birthdate' => null,
            'gender' => '',
            'expected_status' => false,
        ];

        yield [
            'city' => 'city',
            'birthdate' => null,
            'gender' => '',
            'expected_status' => true,
        ];

        yield [
            'city' => null,
            'birthdate' => Carbon::now(),
            'gender' => '',
            'expected_status' => true,
        ];

        yield [
            'city' => null,
            'birthdate' => null,
            'gender' => (string) Gender::MALE(),
            'expected_status' => true,
        ];
    }

    private function createCityWithName(string $name): City
    {
        $mock = $this->createMock(City::class);
        $mock->method('getName')
            ->willReturn($name);

        return $mock;
    }
}
