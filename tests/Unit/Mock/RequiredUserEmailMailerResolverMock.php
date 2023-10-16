<?php

namespace Tests\Unit\Mock;

use App\Domain\Mailing\RequiredUserEmailMailerResolver;
use App\Module\Mailer\MailerInterface;

class RequiredUserEmailMailerResolverMock extends RequiredUserEmailMailerResolver
{
    private $mailer;

    public function __construct(MailerMock $mailer)
    {
        $this->mailer = $mailer;
    }

    public function resolveMailer(): MailerInterface
    {
        return $this->mailer;
    }
}
