<?php

namespace Domain\Record\CompanyArticle\Validator\Constraint;

use App\Domain\Company\Entity\Company;
use App\Domain\Record\CompanyArticle\Entity\CompanyArticle;
use App\Domain\BusinessSubscription\Restriction\PublicationRestrictionChecker;
use App\Domain\Record\CompanyArticle\Validator\Constraint\CompanyDoesNotExceedLimitOfPublications;
use App\Domain\Record\CompanyArticle\Validator\Constraint\CompanyDoesNotExceedLimitOfPublicationsValidator;
use Carbon\Carbon;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\Validator\Constraint;
use Tests\Unit\Mock\ValidatorExecutionContextMock;

class CompanyDoesNotExceedLimitOfPublicationsValidatorTest extends TestCase
{
    private PropertyAccessorInterface $propertyAccessor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->propertyAccessor = PropertyAccess::createPropertyAccessor();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->propertyAccessor);
    }

    public function testValidationObjectWithNullCompanyMustNotCauseErrors(): void
    {
        $validationObject = $this->createValidationObject(null, null);
        $publicationRestrictionChecker = $this->createMock(PublicationRestrictionChecker::class);
        $executionContext = new ValidatorExecutionContextMock();

        $validator = new CompanyDoesNotExceedLimitOfPublicationsValidator($this->propertyAccessor, $publicationRestrictionChecker);
        $validator->initialize($executionContext);
        $validator->validate($validationObject, new CompanyDoesNotExceedLimitOfPublications());

        $this->assertFalse($executionContext->hasViolations());
    }

    public function testValidationObjectThatHasPublicationRestrictionMustCauseErrors(): void
    {
        $company = $this->createMock(Company::class);

        $validationObject = $this->createValidationObject($company, null);
        $publicationRestrictionChecker = $this->createPublicationRestrictionCheckerReturningFalse();
        $executionContext = new ValidatorExecutionContextMock();

        $validator = new CompanyDoesNotExceedLimitOfPublicationsValidator($this->propertyAccessor, $publicationRestrictionChecker);
        $validator->initialize($executionContext);
        $validator->validate($validationObject, new CompanyDoesNotExceedLimitOfPublications());

        $this->assertTrue($executionContext->hasViolations());
    }

    public function testValidationObjectWithEditedArticleMustNotCauseErrors(): void
    {
        $company = $this->createMock(Company::class);
        $companyArticle = $this->createMock(CompanyArticle::class);

        $companyArticle->method('getCreatedAt')->willReturn(Carbon::now()->subDay());

        $validationObject = $this->createValidationObject($company, $companyArticle);

        $publicationRestrictionChecker = $this->createPublicationRestrictionCheckerReturningTrue();

        $executionContext = new ValidatorExecutionContextMock();

        $validator = new CompanyDoesNotExceedLimitOfPublicationsValidator($this->propertyAccessor, $publicationRestrictionChecker);

        $validator->initialize($executionContext);
        $validator->validate($validationObject, new CompanyDoesNotExceedLimitOfPublications());

        $this->assertFalse($executionContext->hasViolations());
    }

    public function testValidationObjectThatHasNotPublicationRestrictionMustNotCauseErrors(): void
    {
        $company = $this->createMock(Company::class);

        $validationObject = $this->createValidationObject($company, null);
        $publicationRestrictionChecker = $this->createPublicationRestrictionCheckerReturningTrue();
        $executionContext = new ValidatorExecutionContextMock();

        $validator = new CompanyDoesNotExceedLimitOfPublicationsValidator($this->propertyAccessor, $publicationRestrictionChecker);
        $validator->initialize($executionContext);
        $validator->validate($validationObject, new CompanyDoesNotExceedLimitOfPublications());

        $this->assertFalse($executionContext->hasViolations());
    }

    public function testUnsupportedConstraint(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Constraint must be instance of ');

        $validationObject = $this->createValidationObject(null, null);
        $publicationRestrictionChecker = $this->createMock(PublicationRestrictionChecker::class);

        $validator = new CompanyDoesNotExceedLimitOfPublicationsValidator($this->propertyAccessor, $publicationRestrictionChecker);
        $validator->validate($validationObject, $this->createMock(Constraint::class));
    }

    private function createPublicationRestrictionCheckerReturningTrue(): PublicationRestrictionChecker
    {
        $publicationRestrictionChecker = $this->createMock(PublicationRestrictionChecker::class);
        $publicationRestrictionChecker->method('canCompanyPublishArticleAtDateTime')
            ->willReturn(true);

        return $publicationRestrictionChecker;
    }

    private function createPublicationRestrictionCheckerReturningFalse(): PublicationRestrictionChecker
    {
        $publicationRestrictionChecker = $this->createMock(PublicationRestrictionChecker::class);
        $publicationRestrictionChecker->method('canCompanyPublishArticleAtDateTime')
            ->willReturn(false);

        return $publicationRestrictionChecker;
    }

    private function createValidationObject(?Company $company, ?CompanyArticle $companyArticle): object
    {
        return (object) [
            'company' => $company,
            'companyArticle' => $companyArticle,
            'publishAt' => null,
        ];
    }
}
