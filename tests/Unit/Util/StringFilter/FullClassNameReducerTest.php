<?php

namespace Tests\Unit\Util\StringFilter;

use App\Util\StringFilter\FullClassNameReducer;
use Tests\Unit\TestCase;

class FullClassNameReducerTest extends TestCase
{
    /** @var FullClassNameReducer */
    private $reduceClassName;

    protected function setUp(): void
    {
        parent::setUp();

        $this->reduceClassName = new FullClassNameReducer();
    }

    public function testFullClassNameMustBeReducedToShortName(): void
    {
        $className = 'Some\Long\Class\Name';

        $actualName = call_user_func($this->reduceClassName, $className);

        $this->assertEquals(
            '<span title="Some\Long\Class\Name">Name</span>',
            $actualName
        );
    }

    public function testFullClassNameContainsSomeOtherInformationAlsoShouldBeReduced(): void
    {
        $className = 'Some\Long\Class\Name::additionalInformation';

        $actualName = call_user_func($this->reduceClassName, $className);

        $this->assertEquals(
            '<span title="Some\Long\Class\Name::additionalInformation">Name::additionalInformation</span>',
            $actualName
        );
    }

    public function testManyFullClassNameShouldBeReduced(): void
    {
        $classNames = 'Some\Long\ClassName1 Some\Long\ClassName2';

        $actualNames = call_user_func($this->reduceClassName, $classNames);

        $this->assertEquals(
            '<span title="Some\Long\ClassName1 Some\Long\ClassName2">ClassName1 ClassName2</span>',
            $actualNames
        );
    }

    public function testNotFullClassNameShouldStayImmutable(): void
    {
        $className = 'ShortClassName';

        $actualName = call_user_func($this->reduceClassName, $className);

        $this->assertEquals($className, $actualName);
    }
}
