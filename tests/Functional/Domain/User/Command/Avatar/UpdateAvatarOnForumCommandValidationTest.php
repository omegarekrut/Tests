<?php

namespace Tests\Functional\Domain\User\Command\Avatar;

use App\Domain\User\Command\Avatar\UpdateAvatarOnForumCommand;
use App\Domain\User\Entity\User;
use App\Domain\User\Entity\ValueObject\Avatar;
use App\Util\ImageStorage\Image;
use Tests\Functional\ValidationTestCase;

class UpdateAvatarOnForumCommandValidationTest extends ValidationTestCase
{
    public function testValidationShouldBePassedForCorrectFilledCommand(): void
    {
        $avatar = $this->createMock(Avatar::class);
        $avatar->method('getImage')->willReturn(new Image('some avatar'));

        $user = $this->createMock(User::class);
        $user->method('getAvatar')->willReturn($avatar);

        $command = new UpdateAvatarOnForumCommand($user);

        $this->getValidator()->validate($command);

        $this->assertEmpty($this->getValidator()->getLastErrors());
    }

    public function testWithUserWithoutAvatar(): void
    {
        $user = $this->createMock(User::class);
        $user->method('getAvatar')->willReturn(null);

        $command = new UpdateAvatarOnForumCommand($user);

        $this->getValidator()->validate($command);

        $this->assertFieldInvalid('userAvatar', 'Значение не должно быть null.');
    }
}
