<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

#[AsCommand(
    name: 'lc:email',
    description: 'Envoie un email de test via Mailtrap',
)]
class SendTestEmailCommand extends Command
{
    private MailerInterface $mailer;
    private string $mailTo;

    public function __construct(MailerInterface $mailer)
    {
        parent::__construct();
        $this->mailer = $mailer;
        $this->mailTo = $_ENV['MAIL_TO'] ?? 'test@example.com';
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $email = (new Email())
            ->from('no-reply@example.com')
            ->to($this->mailTo)
            ->subject('Test Symfony Mailtrap')
            ->text('Si tu lis ce message, l’envoi de mail fonctionne')
            ->html('<p> <strong>Tout fonctionne !</strong> Tu as bien reçu cet email grâce à Mailtrap.</p>');

        $this->mailer->send($email);

        $output->writeln('<info>Email envoyé à ' . $this->mailTo . '</info>');

        return Command::SUCCESS;    
    }
}
