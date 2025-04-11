<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\HttpFoundation\Response;



class StatActionController extends AbstractController
{
    #[Route('/generate-excel', name: 'generate_excel')]
    public function generateExcel(): Response
    {
        $projectPath = dirname($this->getParameter('kernel.project_dir')) . '/image-api';
        $consolePath = $projectPath . '/bin/console';

        $process = new Process(['php', $consolePath, 'lc:excel', '--out', $projectPath . '/public/monfichier2.xlsx']);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        $this->addFlash('success', 'Fichier Excel généré dans le dossier : project-symfony/image-api/public');
        return $this->redirectToRoute('admin_stats');
    }

    #[Route('/send-stats-email', name: 'send_stats_email')]
    public function sendStatsEmail(): RedirectResponse
    {
        $process = new Process(['php', 'bin/console', 'lc:email'], 'C:\Users\Axel\project-symfony\image-api');
        $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        $this->addFlash('success', 'Email envoyé avec succès !');
        return $this->redirectToRoute('admin_stats');
    }
}
