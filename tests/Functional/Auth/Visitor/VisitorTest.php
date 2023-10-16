<?php

namespace Tests\Functional\Auth\Visitor;

use App\Auth\Visitor\Profile\ProfileFactoryInterface;
use App\Auth\Visitor\Visitor;
use App\Domain\Company\Entity\Company;
use App\Domain\WaterLevel\Entity\GaugingStation;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Authentication\Token\AnonymousToken;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Tests\DataFixtures\ORM\Company\Company\LoadCompanyWithOwner;
use Tests\DataFixtures\ORM\Region\Region\LoadNovosibirskRegion;
use Tests\DataFixtures\ORM\User\LoadTestUser;
use Tests\DataFixtures\ORM\WaterLevel\LoadBerdskGaugingStation;
use Tests\DataFixtures\ORM\WaterLevel\LoadNovosibirskGaugingStation;
use Tests\Functional\TestCase;

/**
 * @group visitor
 * @group auth
 * @group water-level
 */
class VisitorTest extends TestCase
{
    /**
     * @var Visitor
     */
    private $visitor;

    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * @var ProfileFactoryInterface
     */
    private $profileFactory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->visitor = $this->getContainer()->get('visitor');
        $this->tokenStorage = $this->getContainer()->get('security.token_storage');
        $this->profileFactory = $this->getContainer()->get(ProfileFactoryInterface::class);
    }

    protected function tearDown(): void
    {
        unset(
            $this->visitor,
            $this->tokenStorage,
            $this->profileFactory
        );

        parent::tearDown();
    }

    public function testVisitorMustBeGuestForEmptyTokenStorage(): void
    {
        $this->tokenStorage->setToken(null);

        $this->assertTrue($this->visitor->isGuest());
    }

    public function testVisitorMustBeGuestForTokenStorageWithAnonymousToken(): void
    {
        $anonymousToken = new AnonymousToken('1', 'anon.');
        $this->tokenStorage->setToken($anonymousToken);

        $this->assertTrue($this->visitor->isGuest());
    }

    public function testVisitorCantBeGuestUser(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Visitor is guest');

        $this->tokenStorage->setToken(null);
        $this->visitor->getUser();
    }

    public function testVisitorMustBeUserForUserToken(): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadTestUser::class,
        ])->getReferenceRepository();

        $user = $referenceRepository->getReference(LoadTestUser::USER_TEST);
        $userPasswordToken = new UsernamePasswordToken($user, '', 'main', $user->getRoles());

        $this->tokenStorage->setToken($userPasswordToken);

        $this->assertFalse($this->visitor->isGuest());
        $this->assertTrue($user === $this->visitor->getUser());
    }

    public function testVisitorGetCity(): void
    {
        $this->loadFixtures([
            LoadNovosibirskRegion::class,
        ]);

        $this->assertEquals('Новосибирск', $this->visitor->getCity());
    }

    public function testVisitorGetMaterialsRegionWithoutCookie(): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadNovosibirskRegion::class,
        ])->getReferenceRepository();

        $defaultRegion = $referenceRepository->getReference(LoadNovosibirskRegion::REFERENCE_NAME);

        $this->assertEquals($defaultRegion, $this->visitor->getMaterialsRegion());
    }

    public function testGuestCanHaveProfile(): void
    {
        $expectedProfile = $this->profileFactory->withUser(null);
        $profile = $this->visitor->getProfile();

        $this->assertEquals($expectedProfile->getPercentageOfCompleted(), $profile->getPercentageOfCompleted());
        $this->assertEquals($expectedProfile->getAvatar(), $profile->getAvatar());
        $this->assertEquals($expectedProfile->getCountUnreadPrivateMessages(), $profile->getCountUnreadPrivateMessages());
        $this->assertEquals($expectedProfile->getCountNotification(), $profile->getCountNotification());
        $this->assertEquals($expectedProfile->getCountPrivateMessages(), $profile->getCountPrivateMessages());
    }

    public function testUserCanHaveProfile(): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadTestUser::class,
        ])->getReferenceRepository();

        $user = $referenceRepository->getReference(LoadTestUser::USER_TEST);
        $userPasswordToken = new UsernamePasswordToken($user, '', 'main', $user->getRoles());

        $this->tokenStorage->setToken($userPasswordToken);

        $expectedProfile = $this->profileFactory->withUser($user);
        $profile = $this->visitor->getProfile();

        $this->assertEquals($expectedProfile->getPercentageOfCompleted(), $profile->getPercentageOfCompleted());
        $this->assertEquals($expectedProfile->getAvatar(), $profile->getAvatar());
        $this->assertEquals($expectedProfile->getCountUnreadPrivateMessages(), $profile->getCountUnreadPrivateMessages());
        $this->assertEquals($expectedProfile->getCountNotification(), $profile->getCountNotification());
        $this->assertEquals($expectedProfile->getCountPrivateMessages(), $profile->getCountPrivateMessages());
    }

    public function testGuestCantBeAuthor(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Visitor is guest');

        $this->visitor->getAuthor();
    }

    public function testUserMustBeAuthor(): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadTestUser::class,
        ])->getReferenceRepository();

        $user = $referenceRepository->getReference(LoadTestUser::USER_TEST);
        $userPasswordToken = new UsernamePasswordToken($user, '', 'main', $user->getRoles());

        $this->tokenStorage->setToken($userPasswordToken);

        $author = $this->visitor->getAuthor();

        $this->assertTrue($user === $author);
    }

    public function testVisitorCanAddGaugingStationAsViewed(): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadNovosibirskRegion::class,
            LoadBerdskGaugingStation::class,
        ])->getReferenceRepository();

        /** @var GaugingStation $gaugingStation */
        $gaugingStation = $referenceRepository->getReference(LoadBerdskGaugingStation::REFERENCE_NAME);

        $this->visitor->addViewedGaugingStation($gaugingStation);

        $this->assertTrue($this->visitor->getLatestViewedOrClosestGaugingStations(1)->contains($gaugingStation));
        $this->assertContains($gaugingStation, $this->visitor->getLatestViewedOrClosestGaugingStations(1));
    }

    public function testVisitorCanRemoveGaugingStationFromViewed(): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadNovosibirskRegion::class,
            LoadBerdskGaugingStation::class,
            LoadNovosibirskGaugingStation::class,
        ])->getReferenceRepository();

        /** @var GaugingStation $berdskGaugingStation */
        $berdskGaugingStation = $referenceRepository->getReference(LoadBerdskGaugingStation::REFERENCE_NAME);
        /** @var GaugingStation $novosibirskGaugingStation */
        $novosibirskGaugingStation = $referenceRepository->getReference(LoadNovosibirskGaugingStation::REFERENCE_NAME);

        $this->visitor->addViewedGaugingStation($berdskGaugingStation);
        $this->visitor->addViewedGaugingStation($novosibirskGaugingStation);
        $this->visitor->removeViewedGaugingStation($berdskGaugingStation);

        $this->assertCount(1, $this->visitor->getLatestViewedOrClosestGaugingStations(10));
    }

    public function testCompanyAuthorInSessionStorageWithCompanyAuthor(): void
    {
        $referenceRepository = $this->loadFixtures([
            LoadCompanyWithOwner::class,
        ])->getReferenceRepository();

        $company = $referenceRepository->getReference(LoadCompanyWithOwner::REFERENCE_NAME);
        assert($company instanceof Company);

        $this->visitor->resolveCompanyAuthor($company);

        $this->assertEquals($company, $this->visitor->getCompanyAuthor());
    }

    public function testCompanyAuthorInSessionStorageWithoutCompanyAuthor(): void
    {
        $session = $this->getContainer()->get('session');
        assert($session instanceof SessionInterface);

        $this->visitor->resolveCompanyAuthor();

        $this->assertNull($this->visitor->getCompanyAuthor());
    }
}
