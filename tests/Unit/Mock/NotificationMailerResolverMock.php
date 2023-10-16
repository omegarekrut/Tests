<?php

namespace Tests\Unit\Mock;

use App\Domain\Mailing\NotificationMailerResolver;
use App\Module\Mailer\MailerInterface;

class NotificationMailerResolverMock extends NotificationMailerResolver
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
