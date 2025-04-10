<?php

namespace App\MessageHandler;

use App\Message\SendEmailMessage;
use App\Repository\ImageRepository;
use App\Repository\StatRepository;
use App\Repository\TypeStatRepository;
use App\Enum\TypeStat;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

#[AsMessageHandler]
class SendEmailMessageHandler
{
    public function __construct(
        private MailerInterface $mailer,
        private ImageRepository $imageRepository,
        private StatRepository $statRepository,
        private TypeStatRepository $typeStatRepository,
        private ParameterBagInterface $params
    ) {}

    public function __invoke(SendEmailMessage $message): void
    {
        $typeDownload = $this->typeStatRepository->find(TypeStat::Telechargement->value);

        $images = $this->imageRepository->findAll();
        $stats = [];

        foreach ($images as $image) {
            $count = count($this->statRepository->findByImageAndType($image, $typeDownload));
            $stats[] = [
                'filename' => $image->getFilename(),
                'count' => $count,
            ];
        }

        // Trie décroissant sur le nombre de téléchargements
        usort($stats, fn($a, $b) => $b['count'] <=> $a['count']);
        $top20 = array_slice($stats, 0, 20);

        $html = "<h1>Statistiques hebdomadaires des téléchargements</h1><ul>";
        foreach ($top20 as $stat) {
            $html .= "<li>{$stat['filename']} : {$stat['count']} téléchargements</li>";
        }
        $html .= "</ul>";

        $email = (new Email())
            ->from('no-reply@hubio.local')
            ->to($this->params->get('mail_to'))
            ->subject('Statistiques des téléchargements - Hebdomadaire')
            ->html($html);

        $this->mailer->send($email);
    }
}
