<?php

namespace Tests\Unit\Domain\Ban\Constraint;

use App\Domain\Ban\Validator\Constraint\IpRange;
use App\Domain\Ban\Validator\Constraint\IpRangeValidator;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Context\ExecutionContext;
use Tests\Unit\TestCase;

/**
 * @group ban
 * @group ban-constraint
 */
class IpRangeTest extends TestCase
{
    /** @var IpRangeValidator */
    private $validator;

    /** @var IpRange */
    private $constraint;

    protected function setUp(): void
    {
        parent::setUp();

        $this->validator = new IpRangeValidator('127.0.0.1');
        $this->constraint = new IpRange();
    }

    public function testInvalidIpNetwork(): void
    {
        $this->validator->initialize($this->createExecutionContext(true));
        $this->validator->validate('invalid ip range', $this->constraint);
    }

    public function testInvalidIpMatchWithServerIp(): void
    {
        $this->validator->initialize($this->createExecutionContext(true));
        $this->validator->validate('127.0.0.1', $this->constraint);
    }

    public function testValidIpNetwork(): void
    {
        $this->validator->initialize($this->createExecutionContext(false));
        $this->validator->validate('192.168.0.1/24', $this->constraint);
    }

    public function testInvalidConstraint(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->validator->validate('192.168.0.1/24', $this->createMock(Constraint::class));
    }

    private function createExecutionContext(bool $called): ExecutionContext
    {
        $stub = $this->createMock(ExecutionContext::class);
        $stub
            ->expects($called ? $this->once() : $this->never())
            ->method('addViolation');

        return $stub;
    }
}
