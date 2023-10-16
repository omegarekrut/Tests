<?php

namespace Tests\Traits;

use App\Domain\User\Command\UserRegistration\UserRegisterCommand;
use App\Domain\User\Entity\User;
use App\Domain\User\Entity\UserFactory;
use Tests\Unit\Mock\ClientIpMock;
use Tests\Unit\Mock\NullSaltGenerator;
use Tests\Unit\Mock\UserPasswordEncoderMock;

trait UserGeneratorTrait
{
    public function generateUser(): User
    {
        $command = new UserRegisterCommand();
        $command->username = 'username'.time();
        $command->password = 'password';
        $command->email = 'email'.time().'@email.com';

        $clientIp = new ClientIpMock();
        $userFactory = new UserFactory($clientIp, new UserPasswordEncoderMock(), new NullSaltGenerator());

        return $userFactory->createFromCommand($command);
    }
}
