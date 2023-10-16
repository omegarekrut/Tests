<?php

namespace Tests\Unit\Domain\Company\Collection;

use App\Domain\Company\Collection\EmployeeCollection;
use App\Domain\Company\Entity\Employee;
use App\Domain\User\Entity\User;
use InvalidArgumentException;
use Tests\Unit\TestCase;

class EmployeeCollectionTest extends TestCase
{
    public function testEmployeeCollectionCantBeCreatedWithNotEmployeeElement(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf('EmployeeCollection element must be instance of %s', Employee::class));

        $notEmployeeArray = [$this->createMock(User::class)];
        new EmployeeCollection($notEmployeeArray);
    }

    public function testEmployeeCollectionCanBeCreatedWithEmployeeElement(): void
    {
        $employee = $this->createMock(Employee::class);

        $employeeCollection = new EmployeeCollection([$employee]);

        $this->assertContains($employee, $employeeCollection);
    }

    public function testCanAddEmployeeToEmployeeCollection(): void
    {
        $employee = $this->createMock(Employee::class);

        $employeeCollection = new EmployeeCollection();
        $employeeCollection->add($employee);

        $this->assertContains($employee, $employeeCollection);
    }

    public function testCantAddNotEmployeeToEmployeeCollection(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf('EmployeeCollection element must be instance of %s', Employee::class));

        $notEmployee = $this->createMock(User::class);

        $employeeCollection = new EmployeeCollection();
        $employeeCollection->add($notEmployee);
    }

    public function testCanSetEmployeeToEmployeeCollection(): void
    {
        $employee = $this->createMock(Employee::class);

        $employeeCollection = new EmployeeCollection();
        $employeeCollection->set(1, $employee);

        $this->assertContains($employee, $employeeCollection);
    }

    public function testCantSetNotEmployeeToEmployeeCollection(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf('EmployeeCollection element must be instance of %s', Employee::class));

        $notEmployee = $this->createMock(User::class);

        $employeeCollection = new EmployeeCollection();
        $employeeCollection->set(1, $notEmployee);
    }
}
