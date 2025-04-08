<?php

namespace App\Controller;

use App\Entity\Image;
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
            'url' => 'http://localhost:8002/view/' . $img->getFilename(),
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
    #[Route('/api/image/{filename}', name: 'api_image_view', methods: ['GET'])]
    public function getImage(string $filename, ImageRepository $repo): JsonResponse
    {
        $image = $repo->findOneBy(['filename' => $filename]);
    
        if (!$image) {
            throw new NotFoundHttpException('Image not found');
        }
    
        $data = [
            'id' => $image->getId(),
            'filename' => $image->getFilename(),
            'url' => 'http://localhost:8002/view/' . $image->getFilename(),
        ];
    
        return new JsonResponse($data);
    }

    // Revoie le fichier de l'image directement.
    #[Route('/view/{filename}', name: 'view_image', methods: ['GET'])]
    public function viewImage(string $filename): BinaryFileResponse
    {
        $filePath = $this->getParameter('kernel.project_dir') . '/public/uploads/' . $filename;

        if (!file_exists($filePath)) {
            throw $this->createNotFoundException('Image not found');
        }

        return new BinaryFileResponse($filePath, 200, [], false);
    }

    // Ajoute une donnée dans la table Stat. 
    // $imageId = "l'image concerné
    // $typeId  = "le type de stat"
    public function recordStat(
        int $imageId,
        int $typeId,
        ImageRepository $imageRepo,
        TypeStatRepository $typeStatRepo,
        EntityManagerInterface $em
    ): JsonResponse {
        $image = $imageRepo->find($imageId);
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

    // TODO :   -appeller recordStat dans les routes necessaire.
    //          - ajouter des données à TypeStat par default.
}
