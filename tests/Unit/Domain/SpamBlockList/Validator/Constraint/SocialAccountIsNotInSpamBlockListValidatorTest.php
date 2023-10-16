<?php

namespace Tests\Unit\Domain\SpamBlockList\Validator\Constraint;

use App\Domain\SpamBlockList\Entity\SpamSocialAccount;
use App\Domain\SpamBlockList\Repository\SpamSocialAccountRepository;
use App\Domain\User\Command\UserRegistration\UserRegisterThroughOAuthCommand;
use App\Domain\SpamBlockList\Validator\Constraint\SocialAccountIsNotInSpamBlockList;
use App\Domain\SpamBlockList\Validator\Constraint\SocialAccountIsNotInSpamBlockListValidator;
use InvalidArgumentException;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\Validator\Constraint;
use Tests\Unit\Mock\ValidatorExecutionContextMock;
use Tests\Unit\TestCase;

/**
 * @group spam-block-list
 */
class SocialAccountIsNotInSpamBlockListValidatorTest extends TestCase
{
    private const SPAM_PROVIDER_NAME = 'vkontakte';
    private const SPAM_PROVIDER_UUID = '4815162342';
    private const TRUSTED_PROVIDER_NAME = 'vkontakte';
    private const TRUSTED_PROVIDER_UUID = '1111111111';

    /** @var SocialAccountIsNotInSpamBlockList */
    private $constraint;

    /** @var SocialAccountIsNotInSpamBlockListValidator */
    private $socialAccountIsNotInSpamBlockListValidator;

    /** @var ValidatorExecutionContextMock */
    private $executionContext;

    protected function setUp(): void
    {
        parent::setUp();

        $this->constraint = new SocialAccountIsNotInSpamBlockList([
            'providerNameField' => 'providerName',
            'providerUuidField' => 'providerUuid',
            'message' => 'Аккаунт {{ providerName }} номер {{ providerUuid }} заблокирован за спам.',
        ]);
        $propertyAccessor = new PropertyAccessor();

        $spamSocialAccountRepositoryMock = $this->createMock(SpamSocialAccountRepository::class);
        $spamSocialAccountRepositoryMock->method('findByProviderNameAndUuid')
            ->will($this->returnValueMap([
                [
                    self::SPAM_PROVIDER_NAME,
                    self::SPAM_PROVIDER_UUID,
                    new SpamSocialAccount(self::SPAM_PROVIDER_NAME, self::TRUSTED_PROVIDER_UUID),
                ],
                [
                    self::TRUSTED_PROVIDER_NAME,
                    self::SPAM_PROVIDER_UUID,
                    null,
                ],
            ]));
        $this->socialAccountIsNotInSpamBlockListValidator = new SocialAccountIsNotInSpamBlockListValidator(
            $propertyAccessor,
            $spamSocialAccountRepositoryMock
        );

        $this->executionContext = new ValidatorExecutionContextMock();
        $this->socialAccountIsNotInSpamBlockListValidator->initialize($this->executionContext);
    }

    protected function tearDown(): void
    {
        unset(
            $this->executionContext,
            $this->socialAccountIsNotInSpamBlockListValidator,
            $this->constraint
        );

        parent::tearDown();
    }

    public function testValidatorNotSupportAnotherConstraintType(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf(
            'Constraint must be instance of %s',
            SocialAccountIsNotInSpamBlockList::class
        ));

        $this->socialAccountIsNotInSpamBlockListValidator->validate(
            null,
            $this->createMock(Constraint::class)
        );
    }

    public function testValidationSkippedIfProviderNameIsNull(): void
    {
        $userRegisterThroughOAuthCommand = new UserRegisterThroughOAuthCommand();
        $userRegisterThroughOAuthCommand->providerName = null;
        $userRegisterThroughOAuthCommand->providerUuid = self::SPAM_PROVIDER_UUID;

        $this->socialAccountIsNotInSpamBlockListValidator->validate(
            $userRegisterThroughOAuthCommand,
            $this->constraint
        );

        $this->assertFalse($this->executionContext->hasViolations());
    }

    public function testValidationSkippedIfProviderUuidIsNull(): void
    {
        $userRegisterThroughOAuthCommand = new UserRegisterThroughOAuthCommand();
        $userRegisterThroughOAuthCommand->providerName = self::SPAM_PROVIDER_NAME;
        $userRegisterThroughOAuthCommand->providerUuid = null;

        $this->socialAccountIsNotInSpamBlockListValidator->validate(
            $userRegisterThroughOAuthCommand,
            $this->constraint
        );

        $this->assertFalse($this->executionContext->hasViolations());
    }

    public function testValidationFailIfSocialAccountIsInTheSpamBlockList(): void
    {
        $userRegisterThroughOAuthCommand = new UserRegisterThroughOAuthCommand();
        $userRegisterThroughOAuthCommand->providerName = self::SPAM_PROVIDER_NAME;
        $userRegisterThroughOAuthCommand->providerUuid = self::SPAM_PROVIDER_UUID;

        $this->socialAccountIsNotInSpamBlockListValidator->validate(
            $userRegisterThroughOAuthCommand,
            $this->constraint
        );

        $this->assertTrue($this->executionContext->hasViolations());
    }

    public function testValidationPassWhenSocialAccountIsNotInTheSpamBlockList(): void
    {
        $userRegisterThroughOAuthCommand = new UserRegisterThroughOAuthCommand();
        $userRegisterThroughOAuthCommand->providerName = self::TRUSTED_PROVIDER_NAME;
        $userRegisterThroughOAuthCommand->providerUuid = self::TRUSTED_PROVIDER_UUID;

        $this->socialAccountIsNotInSpamBlockListValidator->validate(
            $userRegisterThroughOAuthCommand,
            $this->constraint
        );

        $this->assertFalse($this->executionContext->hasViolations());
    }
}
