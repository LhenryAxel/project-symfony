<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ImageController extends AbstractController
{
    private HttpClientInterface $client;

    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;
    }

    
	/**
	 * List images
	 */
    #[Route('/images', name: 'images')]
    public function list(): Response
    {
        $response = $this->client->request('GET', 'http://localhost:8002/api/images');
        $images = $response->toArray();
    
        return $this->render('image/list.html.twig', [
            'images' => $images,
        ]);
    }

	/**
	 * Upload an image
	 */
    #[Route('/upload', name: 'image_upload')]
    public function upload(Request $request): Response
    {
        if ($request->isMethod('POST')) {
            $file = $request->files->get('file');

            if ($file) {
                $response = $this->client->request('POST', 'http://localhost:8002/api/upload', [
                    'body' => [
                        'file' => fopen($file->getPathname(), 'r'),
                    ],
                ]);

                if ($response->getStatusCode() === 201) {
                    $this->addFlash('success', 'L\'image a été publiée avec succès.');
                } else {
                    $this->addFlash('error', 'Une erreur est survenue lors de l\'envoi du fichier à l’API.');
                }

                return $this->redirectToRoute('image_upload');
            }

            $this->addFlash('error', 'Aucun fichier n\'a été sélectionné.');
            return $this->redirectToRoute('image_upload');
        }

        return $this->render('image/upload.html.twig');
    }


	/**
	 * View an image
	 */
	#[Route('/images/{filename}', name: 'images_details', methods: ['GET'])]
	public function details(string $filename): Response
	{
		$responseImage = $this->client->request('GET', "http://127.0.0.1:8002/api/image/view/$filename");

		if ($responseImage->getStatusCode() !== 200) {
			return new Response('Erreur lors de la récupération des images', 500);
		}

		$responseStats = $this->client->request('GET', "http://127.0.0.1:8002/api/stat/$filename");

		$image = $responseImage->toArray();
		$stats = $responseStats->toArray();
	
		return $this->render('image/detail.html.twig', [
			'image' => $image,
			'stats' => $stats,
		]);
	}
}
