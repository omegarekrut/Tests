<?php

namespace Tests\Unit\Domain\Company\Entity;

use App\Domain\BusinessSubscription\Collection\CompanySubscriptionCollection;
use App\Domain\Company\Entity\Company;
use App\Domain\Company\Entity\Employee;
use App\Domain\Company\Entity\ValueObject\EmployeeRole;
use App\Domain\User\Entity\User;
use App\Domain\Company\Entity\Rubric;
use Carbon\Carbon;
use Doctrine\Common\Collections\ArrayCollection;
use Ramsey\Uuid\Uuid;
use Tests\Unit\TestCase;

final class CompanyTest extends TestCase
{
    public function testCompanyIsActual(): void
    {
        $company = $this->createCompany();

        $this->assertTrue($company->isActual());
    }

    public function testCompanyIsNotActual(): void
    {
        Carbon::setTestNow(Carbon::now()->subYear(1));

        $company = $this->createCompany();

        Carbon::setTestNow();

        $this->assertFalse($company->isActual());
    }

    public function testCanAddEmployeeToCompanyWhichWillHaveEditorRole(): void
    {
        $company = $this->createCompany();
        $user = $this->createUser(2);

        $company->addEmployee($user);

        $employee = $company->getEmployees()->first();
        assert($employee instanceof Employee);

        $this->assertTrue($company->isEmployee($user));
        $this->assertTrue($employee->getRole()->equals(EmployeeRole::editor()));
    }

    public function testSeeExceptionIfTryToAddEmployeeAsOwnerOfCompany(): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('and can\'t be employee of company');

        $company = $this->createCompany();
        $user = $company->getOwner();

        $company->addEmployee($user);
    }

    public function testSeeExceptionIfTryToAddTheSameEmployeeTwice(): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('is already employee of company');

        $company = $this->createCompany();
        $user = $this->createUser(2);

        $company->addEmployee($user);
        $company->addEmployee($user);
    }

    public function testCanRemoveEmployeeFromCompany(): void
    {
        $company = $this->createCompany();
        $user = $this->createUser(2);

        $company->addEmployee($user);
        $company->removeEmployee($user);

        $this->assertEmpty($company->getEmployees());
    }

    public function testSeeExceptionIfTryToRemoveEmployeeWhichIsNotEmployeeOfCompany(): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('is not employee of company');

        $company = $this->createCompany();

        $company->removeEmployee($this->createUser(2));
    }

    public function testGetSubscriptions(): void
    {
        $company = $this->createCompany();

        $subscriptions = $company->getSubscriptions();

        $this->assertInstanceOf(CompanySubscriptionCollection::class, $subscriptions);
        $this->assertCount(0, $subscriptions);
        $this->assertFalse($subscriptions->hasActiveSubscription());
    }

    private function createCompany(): Company
    {
        $company = new Company(
            Uuid::uuid4(),
            'Company name',
            'Company slug',
            'Company shortUuid',
            'Company scopeActivity',
            new ArrayCollection([$this->createMockRubric()])
        );
        $company->setOwner($this->createUser());

        return $company;
    }

    private function createMockRubric(): Rubric
    {
        return $this->getMockBuilder(Rubric::class)->disableOriginalConstructor()->getMock();
    }

    private function createUser(int $id = 1): User
    {
        return $this->createConfiguredMock(User::class, [
            'getId' => $id,
        ]);
    }
}
