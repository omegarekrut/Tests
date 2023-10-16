<?php

namespace Tests\Unit\Mock;

use App\Domain\Mailing\ServiceMailMailerResolver;
use App\Module\Mailer\MailerInterface;

class ServiceMailMailerResolverMock extends ServiceMailMailerResolver
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
