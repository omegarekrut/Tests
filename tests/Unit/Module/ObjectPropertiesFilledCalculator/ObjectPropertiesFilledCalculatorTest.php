<?php

namespace Tests\Unit\Module\ObjectPropertiesFilledCalculator;

use App\Module\ObjectPropertiesFilledCalculator\ObjectPropertiesFilledCalculator;
use stdClass;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Tests\Unit\TestCase;

/**
 * @group module
 */
class ObjectPropertiesFilledCalculatorTest extends TestCase
{
    private const CHECK_PROPERTIES_FOR_COMPLETION = [
        'login',
        'password',
        'emailAddress',
    ];

    private ObjectPropertiesFilledCalculator $objectPropertiesFilledCalculator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->objectPropertiesFilledCalculator = new ObjectPropertiesFilledCalculator(PropertyAccess::createPropertyAccessor());
    }

    protected function tearDown(): void
    {
        unset($this->objectPropertiesFilledCalculator);

        parent::tearDown();
    }

    /**
     * @dataProvider getTestSubjects
     */
    public function testCalculatePercentage(stdClass $object, int $expectedPercentageOfCompleted): void
    {
        $this->assertEquals(
            $expectedPercentageOfCompleted,
            $this->objectPropertiesFilledCalculator->calculatePercentage($object, self::CHECK_PROPERTIES_FOR_COMPLETION)
        );
    }

    public function getTestSubjects(): \Generator
    {
        yield [
            $this->createTestSubject([
                'login' => '',
                'password' => '',
                'emailAddress' => '',
            ]),
            0,
        ];

        yield [
            $this->createTestSubject([
                'login' => 'foo',
                'password' => '',
                'emailAddress' => '',
            ]),
            33,
        ];

        yield [
            $this->createTestSubject([
                'login' => 'foo',
                'password' => 'bar',
                'emailAddress' => 'foo',
            ]),
            100,
        ];
    }

    public function testForEmptyCheck(): void
    {
        $user = $this->createTestSubject([
            'login' => 'foo',
            'password' => '',
            'emailAddress' => '',
        ]);

        $this->assertEquals(
            100,
            $this->objectPropertiesFilledCalculator->calculatePercentage($user, [])
        );
    }

    /**
     * @param string[] $properties
     */
    private function createTestSubject(array $properties): stdClass
    {
        return (object) $properties;
    }
}
