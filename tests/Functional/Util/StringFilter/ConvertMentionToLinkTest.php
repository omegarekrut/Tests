<?php

namespace Tests\Functional\Util\StringFilter;

use App\Domain\User\Entity\User;
use App\Twig\User\ConvertMentionToLink;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Tests\DataFixtures\ORM\User\LoadAdminUser;
use Tests\DataFixtures\ORM\User\LoadModeratorAdvancedUser;
use Tests\DataFixtures\ORM\User\LoadModeratorUser;
use Tests\DataFixtures\ORM\User\LoadTestUser;
use Tests\Functional\TestCase as FunctionalTestCase;

/**
 * @group twig
 */
class ConvertMentionToLinkTest extends FunctionalTestCase
{
    /** @var UrlGeneratorInterface */
    private $urlGenerator;
    /** @var ConvertMentionToLink */
    private $convertMentionToLink;

    protected function setUp(): void
    {
        parent::setUp();

        $this->urlGenerator = $this->getContainer()->get('router');
        $this->convertMentionToLink = $this->getContainer()->get(ConvertMentionToLink::class);
    }

    protected function tearDown(): void
    {
        unset(
            $this->urlGenerator,
            $this->convertMentionToLink
        );

        parent::tearDown();
    }

    public function testConvertMentionToLinkForOneUser(): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadTestUser::class,
        ])->getReferenceRepository();

        /** @var User $user */
        $user = $referenceRepository->getReference(LoadTestUser::USER_TEST);

        $userProfileUrl = $this->urlGenerator->generate('user_profile', ['user' => $user->getId()]);
        $expectedText = sprintf('text with mention <a href="%s">@%s</a>', $userProfileUrl, $user->getLogin());

        $textWithMentionUser = ($this->convertMentionToLink)(sprintf('text with mention @%s', $user->getLogin()));

        $this->assertEquals($expectedText, $textWithMentionUser);
    }

    public function testConvertMentionToLinkForManyUser(): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadTestUser::class,
            LoadAdminUser::class,
        ])->getReferenceRepository();

        /** @var User $user */
        $user = $referenceRepository->getReference(LoadTestUser::USER_TEST);
        /** @var User $admin */
        $admin = $referenceRepository->getReference(LoadAdminUser::REFERENCE_NAME);

        $userProfileUrl = $this->urlGenerator->generate('user_profile', ['user' => $user->getId()]);;
        $adminProfileUrl = $this->urlGenerator->generate('user_profile', ['user' => $admin->getId()]);;
        $expectedText = sprintf(
            'text with mention <a href="%s">@%s</a>, <a href="%s">@%s</a>',
            $userProfileUrl, $user->getLogin(), $adminProfileUrl, $admin->getLogin()
        );

        $textWithMentionsUsers = ($this->convertMentionToLink)(sprintf(
            'text with mention @%s, @%s', $user->getLogin(), $admin->getLogin()
        ));

        $this->assertEquals($expectedText, $textWithMentionsUsers);
    }

    public function testConvertMentionToLinkForTestWithSimilarUsernames(): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadModeratorAdvancedUser::class,
            LoadModeratorUser::class,
        ])->getReferenceRepository();

        /** @var User $moderatorAdvancedUser */
        $moderatorAdvancedUser = $referenceRepository->getReference(LoadModeratorAdvancedUser::REFERENCE_NAME);
        /** @var User $moderator */
        $moderator = $referenceRepository->getReference(LoadModeratorUser::REFERENCE_NAME);

        $this->assertStringContainsString($moderator->getLogin(), $moderatorAdvancedUser->getLogin());

        $moderatorAdvancedProfileUrl = $this->urlGenerator->generate('user_profile', ['user' => $moderatorAdvancedUser->getId()]);
        $moderatorProfileUrl = $this->urlGenerator->generate('user_profile', ['user' => $moderator->getId()]);

        $expectedText = sprintf(
            '<a href="%s">@%s</a> comment <a href="%s">@%s</a> mentions',
            $moderatorAdvancedProfileUrl, $moderatorAdvancedUser->getLogin(), $moderatorProfileUrl, $moderator->getLogin()
        );

        $actualText = ($this->convertMentionToLink)(sprintf('@%s comment @%s mentions', $moderatorAdvancedUser->getLogin(), $moderator->getLogin()));

        $this->assertEquals($expectedText, $actualText);
    }
}
