<?php

namespace App\Command;

use App\Message\SendEmailMessage;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsCommand(
    name: 'lc:email',
    description: 'Envoie manuellement l’email hebdomadaire avec les statistiques des 20 images les plus téléchargées.',
)]
class LcEmailCommand extends Command
{
    private MessageBusInterface $bus;

    public function __construct(MessageBusInterface $bus)
    {
        parent::__construct();
        $this->bus = $bus;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $this->bus->dispatch(new SendEmailMessage());

        $io->success('L’email a été envoyé manuellement via lc:email.');

        return Command::SUCCESS;
    }
}
