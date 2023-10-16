<?php

namespace Tests\Unit\Module\OAuth\Factory;

use App\Module\OAuth\Entity\ValueObject\Gender;
use App\Module\OAuth\Factory\GoogleUserInformationFactory;
use Tests\Unit\TestCase;

/**
 * @group oauth
 */
class GoogleUserInformationFactoryTest extends TestCase
{
    use DecoratedPropertyAccessorTrait;

    private const RESPONSE_DATA = [
        'email' => 'email@email.com',
        'verified_email' => true,
        'id' => 'UUID',
        'given_name' => 'first name',
        'family_name' => 'last name',
        'link' => 'http://profile.com',
        'picture' => 'http://photo.com',
        'gender' => 'female',
    ];

    /** @var GoogleUserInformationFactory */
    private $factory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->factory = new GoogleUserInformationFactory($this->createDecoratedPropertyAccessor());
    }

    public function testCreationOAuhUserInformation(): void
    {
        $oauthUserInformation = $this->factory->createOAuthUserInformationByData(self::RESPONSE_DATA);

        $this->assertEquals('UUID', $oauthUserInformation->getId());
        $this->assertEquals('google', $oauthUserInformation->getProvider());
        $this->assertEquals('email@email.com', $oauthUserInformation->getEmail());
        $this->assertEquals('http://profile.com', $oauthUserInformation->getProfileUrl());
        $this->assertEquals('first name last name', $oauthUserInformation->getName());
        $this->assertEmpty($oauthUserInformation->getCity());
        $this->assertEmpty($oauthUserInformation->getDateBirthday());
        $this->assertEquals(Gender::FEMALE, $oauthUserInformation->getGender());
        $this->assertEquals('http://photo.com', $oauthUserInformation->getProfilePicture());
    }

    public function testCreationOnlyWithRequiredData(): void
    {
        $oauthUserInformation = $this->factory->createOAuthUserInformationByData([
            'id' => 'UUID'
        ]);

        $this->assertEquals('UUID', $oauthUserInformation->getId());
        $this->assertEquals('google', $oauthUserInformation->getProvider());
    }
}
