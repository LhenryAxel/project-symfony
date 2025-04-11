<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\SerializerInterface;
use App\Entity\Image;

#[Route('/images')]
class ImageController extends AbstractController
{
	private HttpClientInterface $client;

	public function __construct(HttpClientInterface $client)
	{
		$this->client = $client;
	}

	#[Route('', name: 'images', methods: ['GET'])]
	public function list(): Response
	{
		$response = $this->client->request('GET', 'http://localhost:8002/api/images');

		if ($response->getStatusCode() !== 200) {
			return new Response('Erreur lors de la récupération des images', 500);
		}

		$images = $response->toArray();
		
		return $this->render('image/list.html.twig', [
			'images' => $images,
		]);
	}


	#[Route('/{filename}', name: 'images_details', methods: ['GET'])]
	public function details(string $filename): Response
	{
		$response = $this->client->request('GET', "http://127.0.0.1:8002/api/image/view/$filename");

		if ($response->getStatusCode() !== 200) {
			return new Response('Erreur lors de la récupération des images', 500);
		}

		$image = $response->toArray();
	
		return $this->render('image/detail.html.twig', [
			'image' => $image,
		]);
	}


	/**
	 * Delete an image
	 */
	#[Route('/delete/{id}', name: 'images_delete', methods: ['GET'])]
	public function delete(Request $request, int $id): Response
	{
		$response = $this->client->request('DELETE', "http://localhost:8002/api/image/delete/$id", [
			'body' => [
				'id' => $id,
			],
		]);

		if ($response->getStatusCode() === 200) {
			return $this->redirectToRoute('images', [
				'success' => true,
			]);
			
		}    

		return new Response('Erreur lors de l\'envois de la suppression vers l’API', 500);
	}
}