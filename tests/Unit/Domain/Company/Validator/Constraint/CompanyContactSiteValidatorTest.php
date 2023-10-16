<?php

namespace Tests\Unit\Domain\Company\Validator\Constraint;

use App\Domain\Company\Validator\Constraint\CompanyContactSite;
use App\Domain\Company\Validator\Constraint\CompanyContactSiteValidator;
use Symfony\Component\Validator\Constraint;
use Tests\Unit\Mock\ValidatorExecutionContextMock;
use Tests\Unit\TestCase;

class CompanyContactSiteValidatorTest extends TestCase
{
    private ValidatorExecutionContextMock $executionContext;
    private CompanyContactSite $constraint;
    private CompanyContactSiteValidator $validator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->constraint = new CompanyContactSite();
        $this->executionContext = new ValidatorExecutionContextMock();
        $this->validator = new CompanyContactSiteValidator();

        $this->validator->initialize($this->executionContext);
    }

    protected function tearDown(): void
    {
        unset(
            $this->executionContext,
            $this->constraint,
            $this->validator
        );

        parent::tearDown();
    }

    public function testConstraintMustBeRightInstance(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->validator->validate('site url', $this->createMock(Constraint::class));
    }

    public function testValidationMustBeSkippedForNullValue(): void
    {
        $this->validator->validate(null, $this->constraint);

        $this->assertFalse($this->executionContext->hasViolations());
    }

    /**
     * @dataProvider getValidSites
     */
    public function testSiteIsValid(string $siteUrl): void
    {
        $this->validator->validate($siteUrl, $this->constraint);

        $this->assertFalse($this->executionContext->hasViolations());
    }

    public function getValidSites(): \Generator
    {
        yield ['https://valid-site.com'];

        yield ['http://валидный-сайт.рф/'];

        yield ['https://valid-site-with-path.com/path/to/page'];

        yield ['HTTP://valid-site.com'];

        yield ['HTTPS://valid-site-with-path.com/path/to/page'];

        yield ['HTtp://valid-site.com'];

        yield ['htTPS://valid-site-with-path.com/path/to/page'];
    }

    /**
     * @dataProvider getInvalidSites
     */
    public function testSiteIsInvalid(string $siteUrl): void
    {
        $this->validator->validate($siteUrl, $this->constraint);

        $this->assertTrue($this->executionContext->hasViolations());
    }

    public function getInvalidSites(): \Generator
    {
        yield ['site-without-scheme.com'];

        yield ['https://site-with-invalid-characters-in-domain.c0m'];

        yield ['https://site-with-too-long-domain.coooooooom'];

        yield ['https://site-with-[invalid]-(characters)-in-domain-name.com'];
    }
}
