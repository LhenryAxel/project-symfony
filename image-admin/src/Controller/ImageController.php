<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\File\UploadedFile;

#[Route('/images')]
class ImageController extends AbstractController
{
	private HttpClientInterface $client;

	public function __construct(HttpClientInterface $client)
	{
		$this->client = $client;
	}

	#[Route('/', name: 'images', methods: ['GET'])]
	public function list(): Response
	{
		$response = $this->client->request('GET', 'http://localhost:8002/api/images');
		$images = $response->toArray();
	
		return $this->render('image/list.html.twig', [
			'images' => $images,
		]);
	}


	#[Route('/{id}', name: 'images_details', methods: ['GET'])]
	public function details(int $id): Response
	{
		$response = $this->client->request('GET', "http://localhost:8002/api/images/$id");

		$image = $response->toArray();
	
		return $this->render('image/list.html.twig', [
			'image' => $image,
		]);
	}


	/**
	 * Delete an image
	 */
	#[Route('/{id}', name: 'images_delete', methods: ['DELETE'])]
	public function delete(Request $request, int $id): Response
	{
		$file = $request->files->get('file');

		if ($file) {
			$response = $this->client->request('DELETE', 'http://localhost:8002/api/images', [
				'body' => [
					'id' => $id,
				],
			]);

			if ($response->getStatusCode() === 201) {
				return $this->render('image/delete.html.twig', [
					'success' => true
				]);
			}    

			return new Response('Erreur lors de l\'envois de la suppression vers l’API', 500);
		}

		return $this->render('image/delete.html.twig');
	}
}