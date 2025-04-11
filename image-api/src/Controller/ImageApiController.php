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
use Symfony\Component\Filesystem\Filesystem;


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
            'url' => 'http://localhost:8002/api/image/url/' . $img->getFilename(),
        ], $images);

        return new JsonResponse($data);
    }

    // Renvoie la liste complête des images coté admin.
    #[Route('/api/admin/images', name: 'api_images_admin', methods: ['GET'])]
    public function getImagesAdmin(ImageRepository $repo): JsonResponse
    {
        $images = $repo->findAll();

        $data = array_map(fn($img) => [
            'id' => $img->getId(),
            'filename' => $img->getFilename(),
            'url' => 'http://localhost:8002/api/admin/image/url/' . $img->getFilename(),
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
            'url' => 'http://localhost:8002/api/image/url/' . $image->getFilename(),
        ];

        $recordStatResponse = $this->recordStat($filename, TypeStat::Vue->value, $imageRepo, $typeStatRepo, $em);
        if ($recordStatResponse->getStatusCode() != 201) {
            return json_decode($recordStatResponse->getContent(), true);
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

    //Download
    #[Route('/api/image/download/{filename}', name: 'api-image-download', methods: ['GET'])]
    public function getDownloadImage(
        string $filename,
        ImageRepository $imageRepo,
        TypeStatRepository $typeStatRepo,
        EntityManagerInterface $em
    ): BinaryFileResponse {
        $filePath = $this->getParameter('kernel.project_dir') . '/public/uploads/' . $filename;

        if (!file_exists($filePath)) {
            throw $this->createNotFoundException('File not found');
        }

        $recordStatResponse = $this->recordStat($filename, TypeStat::Telechargement->value, $imageRepo, $typeStatRepo, $em);
        if ($recordStatResponse->getStatusCode() !== 201) {
            throw new \RuntimeException('Erreur serveur lors de l\'enregistrement de la stat de téléchargement');
        }

        $response = new BinaryFileResponse($filePath);
        $response->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            $filename
        );

        return $response;
    }

       // Renvoie les info d'une Image basé sur son nom de fichier. Coté admin.
       #[Route('/api/admin/image/view/{filename}', name: 'api-image-view-admin', methods: ['GET'])]
       public function getImageDataAdmin(
           string $filename,
           ImageRepository $imageRepo,
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
               'url' => 'http://localhost:8002/api/admin/image/url/' . $image->getFilename(),
           ];
       
           return new JsonResponse($data);
       }
   
       // Revoie le fichier de l'image directement. Coté admin.
       #[Route('/api/admin/image/url/{filename}', name: 'api-image-url-admin', methods: ['GET'])]
       public function getImageOnlyAdmin(
           string $filename,
           ImageRepository $imageRepo,
           EntityManagerInterface $em
           ): BinaryFileResponse
       {
           $filePath = $this->getParameter('kernel.project_dir') . '/public/uploads/' . $filename;
   
           if (!file_exists($filePath)) {
               throw $this->createNotFoundException('Image not found');
           }
   
           return new BinaryFileResponse($filePath, 200, [], false);
       }

    // Revoie les stats pour toutes les images
    #[Route('/api/stat/all', name: 'api-stat_all', methods: ['GET'])]
    public function getAllImageStats(
        TypeStatRepository $typeStatRepo,
        ImageRepository $imageRepo,
        StatRepository $statRepo
    ): JsonResponse {
        $images = $imageRepo->findAll();

        $typeView = $typeStatRepo->find(TypeStat::Vue->value);
        $typeRequest = $typeStatRepo->find(TypeStat::RequeteUrl->value);
        $typeDownload = $typeStatRepo->find(TypeStat::Telechargement->value);

        $data = [];

        foreach ($images as $image) {
            $statsForView = $statRepo->findByImageAndType($image, $typeView);
            $statsForRequest = $statRepo->findByImageAndType($image, $typeRequest);
            $statsForDownload = $statRepo->findByImageAndType($image, $typeDownload);

            $data[] = [
                'filename' => $image->getFilename(),
                'view' => count($statsForView),
                'request' => count($statsForRequest),
                'download' => count($statsForDownload),
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
    ): JsonResponse {
        $image = $imageRepo->findOneBy(['filename' => $filename]);

        if (!$image) {
            throw new NotFoundHttpException('Image not found');
        }

        $typeView = $typeStatRepo->find(TypeStat::Vue->value);
        $typeRequest = $typeStatRepo->find(TypeStat::RequeteUrl->value);
        $typeDownload = $typeStatRepo->find(TypeStat::Telechargement->value);

        $statsForView = $statRepo->findByImageAndType($image, $typeView);
        $statsForRequest = $statRepo->findByImageAndType($image, $typeRequest);
        $statsForDownload = $statRepo->findByImageAndType($image, $typeDownload);

        $data = [
            'filename' => $image->getFilename(),
            'view' => count($statsForView),
            'request' => count($statsForRequest),
            'download' => count($statsForDownload),
        ];

        return new JsonResponse($data);
    }

    // Supprime une image via son Id.
    #[Route('/api/image/delete/{id}', name: 'api_delete_image', methods: ['DELETE'])]
    public function deleteImage(
        int $id,
        ImageRepository $imageRepo,
        StatRepository $statRepo,
        EntityManagerInterface $em
    ): JsonResponse {
        $image = $imageRepo->find($id);

        if (!$image) {
            throw new NotFoundHttpException('Image not found');
        }

        $stats = $statRepo->findBy(['image' => $image]);
        foreach ($stats as $stat) {
            $em->remove($stat);
        }
        
        // Suppression du fichier image physique
        $filesystem = new Filesystem();
        $imagePath = $this->getParameter('kernel.project_dir') . '/public/uploads/' . $image->getFilename();
        
        if ($filesystem->exists($imagePath)) {
            $filesystem->remove($imagePath);
        }

        $em->remove($image);
        $em->flush();

        return new JsonResponse(['message' => 'Image and related stats deleted'], 200);
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
