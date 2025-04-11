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

    #[Route('/images', name: 'images')]
    public function list(): Response
    {
        $response = $this->client->request('GET', 'http://localhost:8002/api/images');
        $images = $response->toArray();
    
        return $this->render('image/list.html.twig', [
            'images' => $images,
        ]);
    }

    #[Route('/upload', name: 'image_upload')]
    public function upload(Request $request): Response
    {
        $file = $request->files->get('file');

        if ($file) {
            $response = $this->client->request('POST', 'http://localhost:8002/api/upload', [
                'body' => [
                    'file' => fopen($file->getPathname(), 'r'),
                ],
            ]);

            if ($response->getStatusCode() === 201) {
                return $this->render('image/upload.html.twig', [
                    'success' => true
                ]);
            }    

            return new Response('Erreur lors de l\'upload vers l’API', 500);
        }

        return $this->render('image/upload.html.twig');
    }


    #[Route('/images/{filename}', name: 'images_details', methods: ['GET'])]
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
}
