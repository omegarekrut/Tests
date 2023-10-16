<?php

namespace Tests\Functional\Domain\User\View\Notification\ReferenceFormatter;

use App\Domain\User\Entity\User;
use App\Domain\User\View\Notification\ConcreteNotificationViewFactory\ReferenceFormatter\InitiatorReferenceFormatter;
use App\Module\Author\AnonymousAuthor;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Tests\DataFixtures\ORM\User\LoadTestUser;
use Tests\Functional\TestCase;

/**
 * @group notification
 */
class InitiatorReferenceFormatterTest extends TestCase
{
    /** @var InitiatorReferenceFormatter */
    private $initiatorReferenceFormatter;
    /** @var UrlGeneratorInterface */
    private $urlGenerator;
    /** @var User */
    private $user;

    protected function setUp(): void
    {
        parent::setUp();

        $referenceRepository = $this->loadFixtures([
            LoadTestUser::class,
        ])->getReferenceRepository();

        $this->initiatorReferenceFormatter = $this->getContainer()->get(InitiatorReferenceFormatter::class);
        $this->user = $referenceRepository->getReference(LoadTestUser::USER_TEST);
        $this->urlGenerator = $this->getContainer()->get('router');
    }

    protected function tearDown(): void
    {
        unset(
            $this->initiatorReferenceFormatter,
            $this->user,
            $this->urlGenerator
        );

        parent::tearDown();
    }

    public function testReferenceToAnonymousInitiatorShouldBeJustName(): void
    {
        $anonymousInitiator = new AnonymousAuthor('some name');
        $expectedReference = "<span>{$anonymousInitiator->getUsername()}</span>";

        $reference = $this->initiatorReferenceFormatter->formatReference($anonymousInitiator);

        $this->assertEquals($expectedReference, $reference);
    }

    public function testReferenceToInitiatorMustContainsInitiatorNameAndProfileUrl(): void
    {
        $expectedInitiatorProfileUrl = $this->urlGenerator->generate('user_profile', ['user' => $this->user->getId()]);
        $reference = $this->initiatorReferenceFormatter->formatReference($this->user);

        $expectedReference = sprintf('<a href="%s">%s</a>', $expectedInitiatorProfileUrl, $this->user->getUsername());

        $this->assertEquals($expectedReference, $reference);
    }

    public function testMultipleInitiatorsReferencesShouldBeDisplayedWithComma(): void
    {
        $firstInitiator = new AnonymousAuthor('some name');
        $secondInitiator = new AnonymousAuthor('some other name');

        $expectedReferences = $this->initiatorReferenceFormatter->formatReference($firstInitiator);
        $expectedReferences .= ', '.$this->initiatorReferenceFormatter->formatReference($secondInitiator);

        $references = $this->initiatorReferenceFormatter->formatReferences([$firstInitiator, $secondInitiator]);

        $this->assertEquals($expectedReferences, $references);
    }

    public function testMoreTwoInitiatorsShouldBeMarketAsOther(): void
    {
        $initiators = [
            new AnonymousAuthor('initiator 1'),
            new AnonymousAuthor('initiator 2'),
            new AnonymousAuthor('initiator 3'),
            new AnonymousAuthor('initiator 4'),
        ];

        $expectedOtherInitiators = count($initiators) - 2;
        $expectedOtherReferences = "<span> и еще $expectedOtherInitiators</span>";

        $references = $this->initiatorReferenceFormatter->formatReferences($initiators);

        $this->assertStringContainsString($expectedOtherReferences, $references);
    }
}
