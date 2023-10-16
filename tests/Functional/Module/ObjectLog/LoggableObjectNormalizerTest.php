<?php

namespace Tests\Functional\Module\ObjectLog;

use App\Domain\User\Entity\User;
use App\Module\ObjectLog\LoggableObjectNormalizer;
use Tests\DataFixtures\ORM\User\LoadTestUser;
use Tests\Functional\TestCase;

/**
 * @group log
 */
class LoggableObjectNormalizerTest extends TestCase
{
    /** @var LoggableObjectNormalizer */
    private $normalizer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->normalizer = $this->getContainer()->get(LoggableObjectNormalizer::class);
    }

    protected function tearDown(): void
    {
        unset($this->normalizer);

        parent::tearDown();
    }

    public function testNormalizeMustReplaceEntityToIdentifier(): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadTestUser::class,
        ])->getReferenceRepository();

        /** @var User $user */
        $user = $referenceRepository->getReference(LoadTestUser::USER_TEST);

        $object = new \stdClass();
        $object->user = $user;

        $normalizedObject = $this->normalizer->normalize($object);

        $expectedNormalizedUser = '('.User::class.') id: '.$user->getId();

        $this->assertEquals($expectedNormalizedUser, $normalizedObject['user']);
    }

    public function testNormalizerShouldStayScalarValuesImmutable(): void
    {
        $object = new \stdClass();
        $object->boolean = true;
        $object->integer = 1;
        $object->float = 1.1;
        $object->string = 'string';

        $normalizedObject = $this->normalizer->normalize($object);

        $this->assertEquals($object->boolean, $normalizedObject['boolean']);
        $this->assertEquals($object->integer, $normalizedObject['integer']);
        $this->assertEquals($object->float, $normalizedObject['float']);
        $this->assertEquals($object->string, $normalizedObject['string']);
    }

    public function testNormalizerShouldStayBullValuesImmutable(): void
    {
        $object = new \stdClass();
        $object->null = null;

        $normalizedObject = $this->normalizer->normalize($object);

        $this->assertEquals($object->null, $normalizedObject['null']);
    }

    public function testNestedArrayShouldBeReplacedWithIterableLabelAndCount(): void
    {
        $object = new \stdClass();
        $object->array = [
            'nested' => 'value',
        ];

        $normalizedObject = $this->normalizer->normalize($object);

        $this->assertEquals('(iterable:1)', $normalizedObject['array']);
    }

    public function testObjectShouldBeReplacedWithObjectLabel(): void
    {
        $object = new \stdClass();
        $object->object = new \stdClass();

        $normalizedObject = $this->normalizer->normalize($object);

        $this->assertEquals('('.\stdClass::class.')', $normalizedObject['object']);
    }

    public function testCallableShouldBeReplacedWithCallableLabel(): void
    {
        $object = new \stdClass();
        $object->callable = [$this, 'testCallableShouldBeReplacedWithCallableLabel'];

        $normalizedObject = $this->normalizer->normalize($object);

        $this->assertEquals('(callable)', $normalizedObject['callable']);
    }

    public function testResourceShouldBeReplacedWithResourceLabel(): void
    {
        $object = new \stdClass();
        $object->resource = tmpfile();

        $normalizedObject = $this->normalizer->normalize($object);

        $this->assertEquals('(resource)', $normalizedObject['resource']);
    }

    public function testDateTimeShouldBeNormalizedAsObjectWithFormattedDate(): void
    {
        $object = new \stdClass();
        $object->dateTime = new \DateTime();

        $normalizedObject = $this->normalizer->normalize($object);
        $expectedNormalizedDateTime = '(DateTime) '.$object->dateTime->format('Y-m-d H:i:s');

        $this->assertEquals($expectedNormalizedDateTime, $normalizedObject['dateTime']);
    }
}
