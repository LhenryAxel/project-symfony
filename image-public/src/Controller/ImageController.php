<?php

namespace App\Controller;

use App\Entity\Image;
use App\Form\ImageType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use App\Repository\ImageRepository;

class ImageController extends AbstractController
{
    #[Route('/upload', name: 'image_upload')]
    public function upload(
        Request $request,
        EntityManagerInterface $entityManager,
        SluggerInterface $slugger
    ): Response {
        $image = new Image();
        $form = $this->createForm(ImageType::class, $image);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $file = $form->get('file')->getData();

            if ($file) {
                $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$file->guessExtension();

                $file->move(
                    $this->getParameter('kernel.project_dir').'/public/uploads',
                    $newFilename
                );

                $image->setFilename($newFilename);
                $image->setUploadedAt(new \DateTimeImmutable());

                $entityManager->persist($image);
                $entityManager->flush();

                return $this->redirectToRoute('image_upload');
            }
        }

        return $this->render('image/upload.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/images', name: 'app_images')]
    public function list(ImageRepository $imageRepository): Response
    {
        $images = $imageRepository->findAll();

        return $this->render('image/list.html.twig', [
            'images' => $images,
        ]);
    }

}
