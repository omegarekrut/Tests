<?php

namespace Tests\DataFixtures\Helper;

use App\Domain\User\Entity\User;
use App\Module\Author\AuthorFactory;
use App\Module\Author\AuthorInterface;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Faker\UniqueGenerator;
use Tests\DataFixtures\ORM\User\LoadMostActiveUser;
use Tests\DataFixtures\ORM\User\LoadNumberedUsers;

class AuthorHelper
{
    private $authorFactory;
    private $generator;

    public function __construct(AuthorFactory $authorFactory, UniqueGenerator $generator)
    {
        $this->authorFactory = $authorFactory;
        $this->generator = $generator;
    }

    public function createAnonymousFromUsername(): AuthorInterface
    {
        return $this->authorFactory->createAnonymousFromUsername($this->generator->userName);
    }

    public function createFromUser(User $user): AuthorInterface
    {
        return $user;
    }

    public function chooseAuthor(AbstractFixture $fixture): AuthorInterface
    {
        return $this->chooseUser($fixture);
    }

    public function chooseUser(AbstractFixture $fixture): AuthorInterface
    {
        FixtureHelperAssertions::assertFixtureDependsOnOtherFixture($fixture, LoadMostActiveUser::class);
        FixtureHelperAssertions::assertFixtureDependsOnOtherFixture($fixture, LoadNumberedUsers::class);

        return $fixture->getReference($this->chooseReferenceUser());
    }

    private function chooseReferenceUser(): string
    {
        return random_int(1, 5) === 1 ? LoadMostActiveUser::USER_MOST_ACTIVE : LoadNumberedUsers::getRandReferenceName();
    }
}
