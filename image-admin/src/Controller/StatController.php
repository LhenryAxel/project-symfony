<?php

namespace App\Controller;

use DateTime;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/stat')]
final class StatController extends AbstractController
{
	/**
	 * Main statistics page
	 */
	#[Route('/', name: 'app_stat')]
	public function index(): Response
	{
		$stats = [];
		for ($i = 0; $i < rand(1, 1000); $i++) {
			$stats[] = new class {
				public int $id = 0;
				public DateTime $createdAt;
				public $type = null;

				public function __construct() {
					$this->id = rand(1, 1000);
					$this->createdAt = new DateTime();
					$this->type = new class {
						public int $id = 0;
						public string $name = 'connard';
						public string $description = '';

						public function __construct() {
							$this->id = rand(1, 1000);
						}
					};
				}
			};
		}

		// Sort the stats by createdAt

		return $this->render('stat/index.html.twig', [
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
