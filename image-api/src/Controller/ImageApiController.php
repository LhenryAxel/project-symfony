<?php

namespace App\Controller;

use App\Entity\Image;
use App\Entity\Stat;
use App\Enum\TypeStat;
use App\Repository\ImageRepository;
use App\Repository\TypeStatRepository;
use App\Repository\StatRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ImageApiController extends AbstractController
{
    // Renvoie la liste complête des images
    #[Route('/api/images', name: 'api_images', methods: ['GET'])]
    public function getImages(ImageRepository $repo): JsonResponse
    {
        $images = $repo->findAll();

        $data = array_map(fn($img) => [
            'id' => $img->getId(),
            'filename' => $img->getFilename(),
            'url' => 'http://localhost:8002/image/url/' . $img->getFilename(),
        ], $images);

        return new JsonResponse($data);
    }

    // Enregistre une Image.
    #[Route('/api/upload', name: 'api_upload', methods: ['POST'])]
    public function apiUpload(Request $request, EntityManagerInterface $em, SluggerInterface $slugger): JsonResponse
    {
        $file = $request->files->get('file');

        if (!$file) {
            return new JsonResponse(['error' => 'No file uploaded'], 400);
        }

        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = $slugger->slug($originalFilename);
        $newFilename = $safeFilename.'-'.uniqid().'.'.$file->guessExtension();

        $file->move($this->getParameter('kernel.project_dir').'/public/uploads', $newFilename);

        $image = new Image();
        $image->setFilename($newFilename);
        $image->setUploadedAt(new \DateTimeImmutable());

        $em->persist($image);
        $em->flush();

        return new JsonResponse(['status' => 'uploaded'], 201);
    }
    
    // Renvoie les info d'une Image basé sur son nom de fichier.
    #[Route('/api/image/view/{filename}', name: 'api_image_view', methods: ['GET'])]
    public function getImageData(
        string $filename,
        ImageRepository $imageRepo,
        TypeStatRepository $typeStatRepo,
        EntityManagerInterface $em
        ): JsonResponse
    {
        $image = $imageRepo->findOneBy(['filename' => $filename]);
    
        if (!$image) {
            throw new NotFoundHttpException('Image not found');
        }
    
        $data = [
            'id' => $image->getId(),
            'filename' => $image->getFilename(),
            'url' => 'http://localhost:8002/image/url/' . $image->getFilename(),
        ];

        $recordStatResponse = $this->recordStat($filename, TypeStat::Vue->value, $imageRepo, $typeStatRepo, $em);
        if ($recordStatResponse->getStatusCode() != 201) {
            return json_decode($response->getContent(), true);
        }
    
        return new JsonResponse($data);
    }

    // Revoie le fichier de l'image directement.
    #[Route('/api/image/url/{filename}', name: 'api-image-url', methods: ['GET'])]
    public function getImageOnly(
        string $filename,
        ImageRepository $imageRepo,
        TypeStatRepository $typeStatRepo,
        EntityManagerInterface $em
        ): BinaryFileResponse
    {
        $filePath = $this->getParameter('kernel.project_dir') . '/public/uploads/' . $filename;

        if (!file_exists($filePath)) {
            throw $this->createNotFoundException('Image not found');
        }
        $recordStatResponse = $this->recordStat($filename, TypeStat::RequeteUrl->value, $imageRepo, $typeStatRepo, $em);
        if ($recordStatResponse->getStatusCode() != 201) {
            throw new \RuntimeException('Erreur serveur lors de la récupération du fichier');
        }

        return new BinaryFileResponse($filePath, 200, [], false);
    }

    // Revoie les stats pour toutes les images
    #[Route('/api/stat/all', name: 'api-stat_all', methods: ['GET'])]
    public function getAllImageStats(
        TypeStatRepository $typeStatRepo,
        ImageRepository $imageRepo,
        StatRepository $statRepo
    ): JsonResponse
    {
        $images = $imageRepo->findAll();
        $typeView = $typeStatRepo->find(TypeStat::Vue->value);
        $typeRequest = $typeStatRepo->find(TypeStat::RequeteUrl->value);
        
        $data = [];

        // Pour chaque image, on récupère les stats
        foreach ($images as $image) {
            $statsForView = $statRepo->findByImageAndType($image, $typeView);
            $statsForRequest = $statRepo->findByImageAndType($image, $typeRequest);

            $data[] = [
                'filename' => $image->getFilename(),
                'view' => count($statsForView),
                'request' => count($statsForRequest),
            ];
        }
        return new JsonResponse($data);
    }


    // Revoie les stats d'une image
    // $filename = "l'image concerné
    #[Route('/api/stat/{filename}', name: 'api-stat_image', methods: ['GET'])]
    public function getImageStat(
        string $filename,
        TypeStatRepository $typeStatRepo,
        ImageRepository $imageRepo,
        StatRepository $statRepo,
        EntityManagerInterface $em
        ): JsonResponse
    {
        $image = $imageRepo->findOneBy(['filename' => $filename]);
        $typeView = $typeStatRepo->find(TypeStat::Vue->value);
        $typeRequest = $typeStatRepo->find(TypeStat::RequeteUrl->value);
    
        if (!$image) {
            throw new NotFoundHttpException('Image not found');
        }

        $statsForView = $statRepo->findByImageAndType($image, $typeView);
        $statsForRequest = $statRepo->findByImageAndType($image, $typeRequest);
    
        $data = [
            'filename' => $image->getFilename(),
            'view' => count($statsForView),
            'request' => count($statsForRequest),
        ];
    
        return new JsonResponse($data);
    }

    // Ajoute une donnée dans la table Stat. 
    // $filename = "l'image concerné
    // $typeId  = "le type de stat"
    public function recordStat(
        string $filename,
        int $typeId,
        ImageRepository $imageRepo,
        TypeStatRepository $typeStatRepo,
        EntityManagerInterface $em
    ): JsonResponse {

        $image = $imageRepo->findOneBy(['filename' => $filename]);
        $type = $typeStatRepo->find($typeId);
    
        if (!$image || !$type) {
            return new JsonResponse(['error' => 'Image or Type not found'], 404);
        }
    
        $stat = new Stat();
        $stat->setImage($image);
        $stat->setIdType($type);
        $stat->setHitAt(new \DateTimeImmutable());
    
        $em->persist($stat);
        $em->flush();
    
        return new JsonResponse(['status' => 'Stat recorded'], 201);
    }
}
