<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;




class StatActionController extends AbstractController
{
    #[Route('/generate-excel', name: 'generate_excel')]
    public function generateExcel(Request $request): Response
    {
        $filename = $request->query->get('filename', 'monfichier2.xlsx');
        $relativePath = $request->query->get('path', 'public');

        $projectPath = dirname($this->getParameter('kernel.project_dir')) . '/image-api';
        $outputPath = $projectPath . '/' . trim($relativePath, '/');
        $consolePath = $projectPath . '/bin/console';

        if (!is_dir($outputPath)) {
            mkdir($outputPath, 0777, true);
        }

        $fullFilePath = $outputPath . '/' . $filename;

        $process = new Process(['php', $consolePath, 'lc:excel', '--out', $fullFilePath]);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        $this->addFlash('success', "Fichier Excel généré : {$relativePath}/{$filename}");
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
