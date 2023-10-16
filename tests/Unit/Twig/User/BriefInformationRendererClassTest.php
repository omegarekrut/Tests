<?php

namespace Tests\Unit\Twig\User;

use App\Domain\User\Entity\User;
use App\Domain\User\Entity\ValueObject\City;
use App\Domain\User\Entity\ValueObject\Gender;
use App\Twig\User\BriefInformationRenderer;
use Carbon\Carbon;
use DateTimeInterface;
use Generator;
use Tests\Unit\TestCase;

class BriefInformationRendererClassTest extends TestCase
{
    private BriefInformationRenderer $filter;

    protected function setUp(): void
    {
        parent::setUp();

        $this->filter = new BriefInformationRenderer();
    }

    protected function tearDown(): void
    {
        unset($this->filter);

        parent::tearDown();
    }

    /**
     * @dataProvider usersWithBriefInformation
     */
    public function testRenderer(?string $city, ?DateTimeInterface $birthdate, string $gender, string $expectedStatus): void
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
            'expected_status' => '',
        ];

        yield [
            'city' => 'city',
            'birthdate' => null,
            'gender' => '',
            'expected_status' => 'city',
        ];

        yield [
            'city' => null,
            'birthdate' => Carbon::create(2019, 5, 9),
            'gender' => '',
            'expected_status' => '9 мая 2019',
        ];

        yield [
            'city' => null,
            'birthdate' => null,
            'gender' => (string) Gender::MALE(),
            'expected_status' => 'мужской.',
        ];

        yield [
            'city' => null,
            'birthdate' => null,
            'gender' => (string) Gender::FEMALE(),
            'expected_status' => 'женский.',
        ];

        yield [
            'city' => 'city',
            'birthdate' => Carbon::create(2019, 5, 9),
            'gender' => (string) Gender::FEMALE(),
            'expected_status' => 'city, 9 мая 2019, женский.',
        ];

        yield [
            'city' => 'city',
            'birthdate' => Carbon::create(2019, 5, 9),
            'gender' => (string) Gender::FEMALE(),
            'expected_status' => 'city, 9 мая 2019, женский.',
        ];

        yield [
            'city' => '<script>city</script>',
            'birthdate' => null,
            'gender' => '',
            'expected_status' => 'city',
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
