<?php

namespace Tests\Unit\Module\OAuth\Storage;

use App\Module\OAuth\Entity\OAuthUserInformation;
use App\Module\OAuth\Exception\OAuthUserInformationNotFoundException;
use App\Module\OAuth\Storage\SessionOAuthUserStorage;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Tests\Unit\TestCase;

/**
 * @group oauth
 */
class SessionOAuthUserStorageTest extends TestCase
{
    public function testStore(): void
    {
        $session = new Session(new MockArraySessionStorage());
        $oauthUserInformation = $this->createMock(OAuthUserInformation::class);

        $storage = new SessionOAuthUserStorage($session);
        $storage->setUserInformation($oauthUserInformation);

        $this->assertEquals($storage->getUserInformation(), $oauthUserInformation);
        $this->assertNotEmpty($session->get('oauth.user.information'));
    }


    public function testEmptyStorage(): void
    {
        $this->expectException(OAuthUserInformationNotFoundException::class);

        $storage = new SessionOAuthUserStorage(new Session(new MockArraySessionStorage()));
        $storage->getUserInformation();
    }
}
