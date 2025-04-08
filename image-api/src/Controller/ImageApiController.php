<?php

namespace App\Controller;

use App\Entity\Image;
use App\Repository\ImageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

class ImageApiController extends AbstractController
{
    #[Route('/api/images', name: 'api_images', methods: ['GET'])]
    public function getImages(ImageRepository $repo): JsonResponse
    {
        $images = $repo->findAll();

        $data = array_map(fn($img) => [
            'id' => $img->getId(),
            'filename' => $img->getFilename(),
            'url' => 'http://localhost:8002/uploads/' . $img->getFilename(),
        ], $images);

        return new JsonResponse($data);
    }

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
}
