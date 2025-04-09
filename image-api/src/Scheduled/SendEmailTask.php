<?php

namespace App\Scheduled;

use App\Message\SendEmailMessage;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Scheduler\Attribute\AsPeriodicTask;

#[AsPeriodicTask('1 week')]
class SendEmailTask
{
    public function __construct(private MessageBusInterface $bus) {}

    public function __invoke(): void
    {
        $this->bus->dispatch(new SendEmailMessage());
    }
}
