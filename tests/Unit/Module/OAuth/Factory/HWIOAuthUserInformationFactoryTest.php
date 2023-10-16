<?php

namespace Tests\Unit\Module\OAuth\Factory;

use App\Module\OAuth\Entity\OAuthUserInformation;
use App\Module\OAuth\Exception\UnsupportedUserResponseException;
use App\Module\OAuth\Factory\HWIOAuthUserInformationFactory;
use App\Module\OAuth\Factory\OAuthUserInformationFactoryInterface;
use HWI\Bundle\OAuthBundle\OAuth\ResourceOwnerInterface;
use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use Tests\Unit\TestCase;

/**
 * @group oauth
 */
class HWIOAuthUserInformationFactoryTest extends TestCase
{
    public function testChoiceFactory(): void
    {
        $authUserInformation = $this->createMock(OAuthUserInformation::class);
        $hwiOAuthUserInformationFactory = new HWIOAuthUserInformationFactory([
            'never call factory' => $this->createOAuthUserInformationFactory(false),
            'called provider' => $this->createOAuthUserInformationFactory(true, $authUserInformation),
            'never call factory' => $this->createOAuthUserInformationFactory(false),
        ]);

        $userResponse = $this->createUserResponse('called provider');
        $this->assertEquals($authUserInformation, $hwiOAuthUserInformationFactory->createByUserResponse($userResponse));
    }

    public function testCantChoiceFactory(): void
    {
        $this->expectException(UnsupportedUserResponseException::class);

        $hwiOAuthUserInformationFactory = new HWIOAuthUserInformationFactory([]);
        $userResponse = $this->createUserResponse('provider');
        $hwiOAuthUserInformationFactory->createByUserResponse($userResponse);
    }

    private function createOAuthUserInformationFactory(bool $called, ?OAuthUserInformation $authUserInformation = null): OAuthUserInformationFactoryInterface
    {
        $stub = $this->createMock(OAuthUserInformationFactoryInterface::class);

        if ($called) {
            $stub
                ->expects($this->once())
                ->method('createOAuthUserInformationByData')
                ->willReturn($authUserInformation);
        } else {
            $stub
                ->expects($this->never())
                ->method('createOAuthUserInformationByData');
        }

        return $stub;
    }

    private function createUserResponse(string $resourceOwnerName): UserResponseInterface
    {
        $resourceOwner = $this->createMock(ResourceOwnerInterface::class);
        $resourceOwner
            ->expects($this->once())
            ->method('getName')
            ->willReturn($resourceOwnerName);

        $userResponse = $this->createMock(UserResponseInterface::class);
        $userResponse
            ->expects($this->any())
            ->method('getData')
            ->willReturn([]);

        $userResponse
            ->expects($this->once())
            ->method('getResourceOwner')
            ->willReturn($resourceOwner);

        return $userResponse;
    }
}
