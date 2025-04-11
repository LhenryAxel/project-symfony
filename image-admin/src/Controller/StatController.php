<?php

namespace App\Controller;

use DateTime;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;


#[Route('/stat')]
final class StatController extends AbstractController
{
	public function __construct(private HttpClientInterface $client) {}

	#[Route('', name: 'admin_stats')]
    public function index(): Response
    {
        $response = $this->client->request('GET', 'http://localhost:8002/api/stat/all');

        if ($response->getStatusCode() !== 200) {
            return new Response("Erreur lors de la récupération des statistiques", 500);
        }

        $stats = $response->toArray();

        return $this->render('stat/stats.html.twig', [
            'stats' => $stats,
        ]);
    }

	#[Route('/stat/usage', name: 'app_stat_usage')]
	public function index2(): Response
	{

		return $this->render('stat/index.html.twig', [
			'controller_name' => 'StatController',
		]);
	}

}
