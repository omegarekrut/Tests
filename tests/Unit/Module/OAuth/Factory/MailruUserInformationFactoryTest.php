<?php

namespace Tests\Unit\Module\OAuth\Factory;

use App\Module\OAuth\Entity\ValueObject\Gender;
use App\Module\OAuth\Factory\MailruUserInformationFactory;
use Tests\Unit\TestCase;

/**
 * @group oauth
 */
class MailruUserInformationFactoryTest extends TestCase
{
    use DecoratedPropertyAccessorTrait;

    private const RESPONSE_DATA = [
        'email' => 'email@email.com',
        'uid' => 'UUID',
        'first_name' => 'first name',
        'last_name' => 'last name',
        'sex' => 1,
        'birthday' => '27.09.2018',
        'has_pic' => 1,
        'pic' => 'http://photo.com',
        'link' => 'http://profile.com',
        'location' => [
            'city' => [
                'name' => 'city',
            ],
        ],
    ];

    /** @var MailruUserInformationFactory */
    private $factory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->factory = new MailruUserInformationFactory($this->createDecoratedPropertyAccessor());
    }

    public function testCreationUserInformationByResponseData(): void
    {
        $oauthUserInformation = $this->factory->createOAuthUserInformationByData(self::RESPONSE_DATA);

        $this->assertEquals('UUID', $oauthUserInformation->getId());
        $this->assertEquals('mailru', $oauthUserInformation->getProvider());
        $this->assertEquals('email@email.com', $oauthUserInformation->getEmail());
        $this->assertEquals('http://profile.com', $oauthUserInformation->getProfileUrl());
        $this->assertEquals('first name last name', $oauthUserInformation->getName());
        $this->assertEquals('city', $oauthUserInformation->getCity());
        $this->assertEquals('2018-09-27', $oauthUserInformation->getDateBirthday()->format('Y-m-d'));
        $this->assertEquals(Gender::FEMALE, $oauthUserInformation->getGender());
        $this->assertEquals('http://photo.com', $oauthUserInformation->getProfilePicture());
    }

    public function testCreationOnlyWithRequiredData(): void
    {
        $oauthUserInformation = $this->factory->createOAuthUserInformationByData([
            'uid' => 'UUID',
        ]);

        $this->assertEquals('UUID', $oauthUserInformation->getId());
        $this->assertEquals('mailru', $oauthUserInformation->getProvider());
    }
}
