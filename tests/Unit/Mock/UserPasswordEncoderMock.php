<?php
namespace Tests\Unit\Mock;

use Symfony\Component\Security\Core\Encoder\PlaintextPasswordEncoder;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class UserPasswordEncoderMock implements UserPasswordEncoderInterface
{
    public function encodePassword(UserInterface $user, $plainPassword)
    {
        return (new PlaintextPasswordEncoder($user))->encodePassword($plainPassword, $user->getSalt());
    }

    public function isPasswordValid(UserInterface $user, $raw)
    {
        return (new PlaintextPasswordEncoder($user))->isPasswordValid($user->getPassword(), $raw, $user->getSalt());
    }
}
